<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Validator;
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
    private $isRequiredString = 'required|string|max:255';
    private $isRequiredCustomString = 'required|string|min:6|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/|confirmed';
    private $isRequiredEmail = 'required|string|email|max:255';
    private $passwordString = 'password';
    private $userAttribute = 'user';
    private $userPassword = 'user password';
    private $oldPassword = 'old_password';
    private $newPassword = 'new_password';
    private $updatedMessage = 'response.messages.updated';
    private $notValidMessage = 'response.messages.not_valid';
    private $status = 'status';
    private $message = 'message';

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
              $this->status => true,
              'code' => __($this->successCode),
              $this->message => __('response.messages.password_email'),
              'token' => $unhashedToken
            ], 200);
    }

    public function resetPassword(Request $request){
        $rules = [
          'email' => $this->isRequiredEmail,
          'password' => $this->isRequiredCustomString,
          'token' => $this->isRequiredString,
        ];

        $validator =  Validator::make($request->all(), $rules);

        if($validator->fails()){
          return $this->jsonValidationError($validator);
        }

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
                      $this->status => true,
                      'code' => __($this->successCode),
                      $this->message => __('response.messages.password_reset')
                    ], 200);
            }
        }

        return $this->jsonResponse(__('response.messages.token_invalid'), __($this->errorCode), 400, [], __($this->error));
    }

    public function changePassword(Request $request){
      $rules = [
        $this->oldPassword =>$this->isRequiredString,
        $this->newPassword =>$this->isRequiredCustomString,
      ];

      $validator =  Validator::make($request->all(), $rules);

      if($validator->fails()){
        return $this->jsonValidationError($validator);
      }

      $password = $request->input($this->oldPassword);

      $user = $request->user();

      if (Hash::check($password, $user->password)) {
        $user->fill([
          $this->passwordString => Hash::make($request->input($this->newPassword)),
        ])->save();

        $response =  (new UserResource($user))
              ->additional([
                $this->status => true,
                'code' => __($this->successCode),
                $this->message => __($this->updatedMessage, ['attr' => $this->userPassword]),
              ], 200);
      }else{
        $response = $this->jsonResponse(__($this->notValidMessage, ['attr' => 'Password you entered']), __($this->errorCode), 400, [], __($this->error));
      }

      return $response;
    }
}
