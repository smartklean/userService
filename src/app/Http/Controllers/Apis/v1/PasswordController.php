<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function sendPasswordResetEmail(Request $request){
      $rules = [
          'email' => 'required|string|email|max:255'
      ];

      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails())
      {
          return response()->json([
              'status' => false,
              'error' => __('response.errors.request'),
              'message' => __('response.messages.validation'),
              'data' => [
                'errors' => $validator->getMessageBag()->toArray()
              ]
          ], 400);
      }

      $email = $request->input('email');

      $user = User::where('email', $email)->first();

      if(!$user){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.not_found', ['attr' => 'user'])
        ], 400);
      }

      $token = str_shuffle(uniqid().uniqid());

      $unhashedToken = $token;

      $token = Hash::make($token);

      $exists = DB::table('password_resets')->where([
          'email' => $email
      ])->first();

      if($exists){
        DB::delete('delete from password_resets where email = ?', [$email]);
      }

      DB::insert('insert into password_resets (email, token, created_at) values (?, ?, ?)', [$email, $token, Carbon::now()]);

      //trigger email notification service

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => __('response.messages.password_email'),
              'token' => $unhashedToken
            ], 200);
    }

    public function resetPassword(Request $request){
        $rules = [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|max:255|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
        {
          return response()->json([
              'status' => false,
              'error' => __('response.errors.request'),
              'message' => __('response.messages.validation'),
              'data' => [
                'errors' => $validator->getMessageBag()->toArray()
              ]
          ], 400);
        }

        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if(!$user){
          return response()->json([
            'status' => false,
            'error' => __('response.errors.request'),
            'message' => __('response.messages.not_found', ['attr' => 'user'])
          ], 400);
        }

        $token = DB::table('password_resets')->where(['email' => $user->email])->first();

        if(isset($token)){
            if(strtotime('-15 minutes') - strtotime(DB::table('password_resets')->where(['email' => $user->email])->first()->created_at) > 0){
                return response()->json([
                  'status' => false,
                  'error' => __('response.errors.request'),
                  'message' => __('response.messages.token_expired')
                ], 400);
            }
        }else{
            return response()->json([
              'status' => false,
              'error' => __('response.errors.request'),
              'message' => __('response.messages.token_invalid')
            ], 400);
        }

        if(Hash::check($request->header('Reset-Token'), $token->token)){
            $user->password = Hash::make($request->input('password'));
            $user->save();

            $token = DB::table('password_resets')->where(['email' => $user->email]);

            if($token->first() !== null)
                $token->delete();

            $credentials = ['email' => $request->input('email'), 'password' => $request->input('password')];

            return (new UserResource($user))
                  ->additional([
                    'status' => true,
                    'message' => __('response.messages.password_reset')
                  ], 200);
        }

        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.token_mismatch')
        ], 400);
    }
}
