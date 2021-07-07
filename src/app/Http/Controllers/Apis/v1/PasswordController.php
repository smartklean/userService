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
use App\Traits\HandlesJsonResponse;

class PasswordController extends Controller
{
    use HandlesJsonResponse;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $error = 'response.errors.request';
    private $notFoundMessage = 'response.messages.not_found';
    private $notFoundError = 'response.errors.not_found';
    private $errorCode = 'response.codes.error';
    private $notFoundErrorCode = 'response.codes.not_found_error';
    private $successCode = 'response.codes.success';
    private $userAttribute = 'user';


    public function sendPasswordResetEmail(Request $request){
      $email = $request->input('email');

      $user = User::where('email', $email)->first();

      if(!$user){
        return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
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

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'code' => __($this->successCode),
              'message' => __('response.messages.password_email'),
              'token' => $unhashedToken
            ], 200);
    }

    public function resetPassword(Request $request){
        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if(!$user){
          return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
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
                      'code' => __($this->successCode),
                      'message' => __('response.messages.password_reset')
                    ], 200);
            }
        }

        return $this->jsonResponse(__('response.messages.token_invalid'), __($this->errorCode), 400, [], __($this->error));
    }
}
