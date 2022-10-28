<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesJsonResponse;
use App\Traits\HandlesRequest;

class VerifyOtpController extends Controller
{
    use HandlesJsonResponse, HandlesRequest;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

     private $otpSentMessage = 'response.messages.verification_code_sent';
     private $verifiedMessage = 'response.messages.verified';
     private $notFoundMessage = 'response.messages.not_found';
     private $notVerifiedMessage = 'response.messages.not_verified';
     private $error = 'response.errors.request';
     private $notFoundError = 'response.errors.not_found';
     private $errorCode = 'response.codes.error';
     private $successCode = 'response.codes.success';
     private $notFoundErrorCode = 'response.codes.not_found_error';
     private $userAttribute = 'user';
     private $isRequired = 'required|string|max:255';

     public function verifyOtp(Request $request){
       $rules = [
         'user_id' => $this->isRequired,
         'otp' => $this->isRequired
       ];

       $validator =  Validator::make($request->all(), $rules);

       if($validator->fails()){
         return $this->jsonValidationError($validator);
       }

       $user = User::where('id', $request->user_id)->first();

       if(!$user){
         return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
       }

       if(Hash::check($request->otp, $user->otp)){
        try {
            $res = $this->call('GET', $request, config('businessws.url').'/api/v1/business/'.$request->businessId.'/setting', [
              'content-type' => 'application/json',
              'accept' => 'application/json'
            ]);
            
            $setting = json_decode($res->getBody())->data;
            if(!$setting->sms_enabled){
                $res = $this->call('GET', $request, config('businessws.url').'/api/v1/business/'.$request->businessId.'/setting/sms', [
                    'content-type' => 'application/json',
                    'accept' => 'application/json'
                  ]);
                  
                  $setting = json_decode($res->getBody())->data;
            }
            $user->phone_number_verified = true;
             $user->otp = null;
             $user->save();

             $response = (new UserResource($user))
                   ->additional([
                     'status' => true,
                     'code' => __($this->successCode),
                     'message' => __($this->verifiedMessage, ['attr' => $this->userAttribute])
                   ], 200);
            
          } catch (Throwable $e) {
            return $this->jsonResponse($e->getMessage(), __($this->errorCode), 500, [], __($this->serverError));
          }
             
       }else{
         $response = $this->jsonResponse(__($this->notVerifiedMessage, ['attr' => $this->userAttribute]), __($this->errorCode), 400, [], __($this->error));
       }

       return $response;
     }

     public function sendOtp(Request $request){
        $rules = [
            'user_id' => $this->isRequired,
          ];
   
          $validator =  Validator::make($request->all(), $rules);
   
          if($validator->fails()){
            return $this->jsonValidationError($validator);
          }
   
          $user = User::where('id', $request->user_id)->first();
   
          if(!$user){
            return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
          }
       //generate otp
        $unhashedOtp = random_int(123456,999999);
        $otp = Hash::make($unhashedOtp);
        $phone_number_verified = false;
        $user->otp = $otp;
        $user->phone_number_verified = $phone_number_verified;
        $user->save();

        //send sms 
       return (new UserResource($user))
             ->additional([
               'status' => true,
               'code' => __($this->successCode),
               'message' => __($this->otpSentMessage),
               'otp' => $unhashedOtp
             ], 200);
     }
}
