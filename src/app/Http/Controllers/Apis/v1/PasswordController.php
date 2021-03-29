<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function __construct(){
      define('ERR', 'response.errors.request');
      define('ERR_NOT_FOUND', 'response.messages.not_found');
    }

    public function sendPasswordResetEmail(Request $request){
      $email = $request->input('email');

      $user = User::where('email', $email)->first();

      if(!$user){
        return response()->json([
          'status' => false,
          'error' => __(ERR),
          'message' => __(ERR_NOT_FOUND, ['attr' => 'user'])
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
        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if(!$user){
          return response()->json([
            'status' => false,
            'error' => __(ERR),
            'message' => __(ERR_NOT_FOUND, ['attr' => 'user'])
          ], 400);
        }

        $token = DB::table('password_resets')->where(['email' => $user->email])->first();

        if(isset($token) && Hash::check($request->input('token'), $token->token)){
            if(strtotime('-15 minutes') - strtotime(DB::table('password_resets')->where(['email' => $user->email])->first()->created_at) < 0){
              $user->password = Hash::make($request->input('password'));
              $user->save();

              $token = DB::table('password_resets')->where(['email' => $user->email]);

              if($token->first() !== null){
                $token->delete();
              }

              return (new UserResource($user))
                    ->additional([
                      'status' => true,
                      'message' => __('response.messages.password_reset')
                    ], 200);
            }
        }

        return response()->json([
          'status' => false,
          'error' => __(ERR),
          'message' => __('response.messages.token_invalid')
        ], 400);
    }
}
