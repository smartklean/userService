<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VerifyEmailController extends Controller
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

     public function verifyEmail(Request $request){
       $user = $request->user();

       if(!$user){
         return response()->json([
           'status' => false,
           'error' => __(ERR),
           'message' => __(ERR_NOT_FOUND, ['attr' => 'user'])
         ], 400);
       }

       if(Hash::check($request->input('email_verification_code'), $user->email_verification_code)){
           if(strtotime('-15 minutes') - strtotime($user->updated_at) < 0){
             $user->email_verification_code = null;
             $user->email_verified_at = Carbon::now();
             $user->save();

             return (new UserResource($user))
                   ->additional([
                     'status' => true,
                     'message' => __('response.messages.verified', ['attr' => 'user'])
                   ], 200);
           }
       }

       return response()->json([
         'status' => false,
         'error' => __(ERR),
         'message' => __('response.messages.not_verified', ['attr' => 'user'])
       ], 400);
     }

     public function resendVerificationCode(Request $request){
       $user = $request->user();

       if(!$user){
         return response()->json([
           'status' => false,
           'error' => __(ERR),
           'message' => __(ERR_NOT_FOUND, ['attr' => 'user'])
         ], 400);
       }

       $unhashedEmailVerificationCode = str_shuffle(uniqid().uniqid());

       $emailVerificationCode = Hash::make($unhashedEmailVerificationCode);

       $emailVerifiedAt = null;

       $user->email_verification_code = $emailVerificationCode;
       $user->email_verified_at = $emailVerifiedAt;

       $user->save();

       // trigger email notification service

       return (new UserResource($user))
             ->additional([
               'status' => true,
               'message' => __('response.messages.verification_code_sent'),
               'email_verification_code' => $unhashedEmailVerificationCode
             ], 200);
     }
}
