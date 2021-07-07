<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesJsonResponse;

class VerifyEmailController extends Controller
{
    use HandlesJsonResponse;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

     private $verificationCodeSentMessage = 'response.messages.verification_code_sent';
     private $verifiedMessage = 'response.messages.verified';
     private $notFoundMessage = 'response.messages.not_found';
     private $notVerifiedMessage = 'response.messages.not_verified';
     private $error = 'response.errors.request';
     private $notFoundError = 'response.errors.not_found';
     private $errorCode = 'response.codes.error';
     private $successCode = 'response.codes.success';
     private $notFoundErrorCode = 'response.codes.not_found_error';
     private $userAttribute = 'user';

     public function verifyEmail(Request $request){
       $user = User::where('email', $request->email)->first();

       if(!$user){
         return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
       }

       if(Hash::check($request->email_verification_code, $user->email_verification_code)){
           if(strtotime('-15 minutes') - strtotime($user->updated_at) < 0){
             $user->email_verification_code = null;
             $user->email_verified_at = Carbon::now();
             $user->save();

             return (new UserResource($user))
                   ->additional([
                     'status' => true,
                     'code' => __($this->successCode),
                     'message' => __($this->verifiedMessage, ['attr' => $this->userAttribute])
                   ], 200);
           }
       }

       return $this->jsonResponse(__($this->notVerifiedMessage, ['attr' => $this->userAttribute]), __($this->errorCode), 400, [], __($this->error));
     }

     public function resendVerificationCode(Request $request){
       $user = $request->user();

       $unhashedEmailVerificationCode = str_shuffle(uniqid().uniqid());

       $emailVerificationCode = Hash::make($unhashedEmailVerificationCode);

       $emailVerifiedAt = null;

       $user->email_verification_code = $emailVerificationCode;
       $user->email_verified_at = $emailVerifiedAt;

       $user->save();

       return (new UserResource($user))
             ->additional([
               'status' => true,
               'code' => __($this->successCode),
               'message' => __($this->verificationCodeSentMessage),
               'email_verification_code' => $unhashedEmailVerificationCode
             ], 200);
     }
}
