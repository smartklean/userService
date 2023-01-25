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
use App\Traits\SendsNotification;
use App\Traits\FetchesBusiness;
use Throwable;
use Illuminate\Support\Facades\Log as Loggable;

class VerifyOtpController extends Controller
{
    use HandlesJsonResponse, HandlesRequest, SendsNotification, FetchesBusiness;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

     private $otpSentMessage = 'response.messages.verification_code_sent';
     private $verifiedMessage = 'response.messages.verified';
     private $notFoundMessage = 'response.messages.not_found';
     private $notVerifiedMessage = 'response.messages.not_verified';
     private $tokenInvalidMessage = 'response.messages.token_invalid';
     private $error = 'response.errors.request';
     private $notFoundError = 'response.errors.not_found';
     private $errorCode = 'response.codes.error';
     private $successCode = 'response.codes.success';
     private $notFoundErrorCode = 'response.codes.not_found_error';
     private $userAttribute = 'user';
     private $isRequiredNumeric = 'required|numeric';

     public function verifyOtp(Request $request){
       $rules = [
         'otp' => $this->isRequiredNumeric
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
        if(Carbon::parse($user->otp_created_at)->diffInMinutes(Carbon::now()) <= 15){
          try {
              $request->merge([
                'status' => true,
              ]);

              $res = $this->call('PUT', $request, config('businessws.url').'/api/v1/business/'.$request->businessId.'/sms', [
                'content-type' => 'application/json',
                'accept' => 'application/json'
                ]);

               $user->fill([
                 'phone_number_verified' => true,
                 'otp' => null,
                 'otp_created_at' => null,
               ])->save();

               $response = (new UserResource($user))
                     ->additional([
                       'status' => true,
                       'code' => __($this->successCode),
                       'message' => __($this->verifiedMessage, ['attr' => 'phone number']),
                     ], 200);

            } catch (Throwable $e) {
              Loggable::error($e);
              return $this->jsonResponse($e->getMessage(), __($this->errorCode), 500, [], __('Something went wrong.'));
            }
        }else{
          $response = $this->jsonResponse(__($this->tokenInvalidMessage), __($this->errorCode), 400, [], __($this->error));
        }
       }else{
         $response = $this->jsonResponse(__($this->notVerifiedMessage, ['attr' => $this->userAttribute]), __($this->errorCode), 400, [], __($this->error));
       }

       return $response;
     }

     public function sendOtp(Request $request){
        $user = User::where('id', $request->user_id)->first();

        if(!$user){
          return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
        }

        $unhashedOtp = random_int(123456,999999);

        $otp = Hash::make($unhashedOtp);

        $user->fill([
          'otp' => $otp,
          'otp_created_at' => Carbon::now(),
          'phone_number_verified' => false,
        ])->save();

        $business = $this->fetchBusiness(new Request([]), $request->business_id);

        $merchantData = [
          [
            'phone_number' => $user->phone_number
          ]
        ];

        $templateData = [
          'first_name' => $user->first_name,
          'business_name' => $business->name,
          'otp' => $unhashedOtp
        ];

        $res = $this->sendNotification('Verify_Phone_Number_OTP', $merchantData, $templateData, ['sms']);

        Loggable::info($res);

        return $this->jsonResponse('An OTP has been sent to your phone number.');
     }
}
