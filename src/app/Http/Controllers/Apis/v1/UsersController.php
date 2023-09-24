<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesUser;
use App\Traits\HandlesJsonResponse;
use App\Traits\HandlesRequest;
use Throwable;
use Illuminate\Support\Facades\Log as Loggable;

class UsersController extends Controller
{
    use HandlesUser, HandlesJsonResponse, HandlesRequest;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $foundMultipleMessage = 'response.messages.found_multiple';
    private $addedMessage = 'response.messages.added';
    private $foundMessage = 'response.messages.found';
    private $updatedMessage = 'response.messages.updated';
    private $deletedMessage = 'response.messages.deleted';
    private $notFoundMessage = 'response.messages.not_found';
    private $notFoundError = 'response.errors.not_found';
    private $notFoundErrorCode = 'response.codes.not_found_error';
    private $successCode = 'response.codes.success';
    private $userAttribute = 'user';
    private $walletAttribute = 'User Wallet';
    private $usersAttribute = 'users';
    private $isRequiredString = 'required|string|max:255';
    private $isNullableString = 'nullable|string|max:255';
    private $emailVerificationCodeString = 'email_verification_code';
    private $firstName = 'first_name';
    private $lastName = 'last_name';
    private $email = 'email';
    private $passwordString = 'password';
    private $phoneNumber = 'phone_number';
    private $status = 'status';
    private $message = 'message';

    public function fetch(Request $request){
      if($request->email){
        return $this->findByEmail($request->email);
      }

      $users = $this->fetchUser($request);

      return UserResource::collection($users)
              ->additional([
                $this->status => true,
                'code' => __($this->successCode),
                $this->message => __($this->foundMultipleMessage, ['attr' => $this->usersAttribute]),
              ], 200);
    }

    public function fetchSingle($id){
      $user = User::find($id);

      if(!$user){
        return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
      }

      return (new UserResource($user))
            ->additional([
              $this->status => true,
              'code' => __($this->successCode),
              $this->message => __($this->foundMessage, ['attr' => $this->userAttribute]),
            ], 200);
    }

    public function store(Request $request){
      $rules = [
        $this->firstName => $this->isRequiredString,
        $this->lastName => $this->isRequiredString,
        $this->passwordString => $this->isNullableString.'|confirmed',
        $this->email => 'required|email|max:255|unique:users',
        $this->phoneNumber => $this->isRequiredString.'|unique:users',
        
      ];

      $validator =  Validator::make($request->all(), $rules);

      if($validator->fails()){
        return $this->jsonValidationError($validator);
      }
      $password = Hash::make($request->input($this->passwordString));
      $unhashedEmailVerificationCode = str_shuffle(uniqid().uniqid());

      $emailVerificationCode = Hash::make($unhashedEmailVerificationCode);
     
      $emailVerifiedAt = Carbon::now();

      $user = User::create([
        $this->firstName => $request->input($this->firstName),
        $this->lastName => $request->input($this->lastName),
        $this->email => $request->input($this->email),
        'email_verified_at' => $emailVerifiedAt,
        $this->passwordString => $password,
        $this->phoneNumber => $request->input($this->phoneNumber)
      ]);
      if($user){

        try {

          $request->merge([
            'user_id'=> $user->id,
            'account_name'=>$user->first_name .' '. $user->last_name,
          ]);
          $res = $this->call('POST', $request, config('authorizationws.url').'/api/v1/wallet/', [
          'content-type' => 'application/json',
          'accept' => 'application/json'
          ]);
  
          $response = json_decode($res->getBody());

          $user->wallet = $response->data;

        } catch (Throwable $e) {
          Loggable::error($e);
          return $this->jsonResponse($e->getMessage(), __($this->errorCode), 500, [], __('Something went wrong.'));
        }
        
      }

      return (new UserResource($user))
            ->additional([
              $this->status => true,
              'code' => __($this->successCode),
              $this->message => __($this->addedMessage, ['attr' => 'user']),
              $this->emailVerificationCodeString => $unhashedEmailVerificationCode
            ], 201);
    }

    public function fetchUserWallet(Request $request, $userId){
      $user = User::find($userId);

      if(!$user){
        return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
      }

      $request = new Request([]);
      $res = $this->call('GET', $request, config('authorizationws.url').'/api/v1/wallet/user/'.$userId, [
        'content-type' => 'application/json',
        'accept' => 'application/json'
        ]);

        $response = json_decode($res->getBody());
        Loggable::info(json_encode($response));

        $wallet = $response->data;

        $response = !$wallet ? $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->walletAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError))
        : $this->jsonResponse(__($this->foundMessage, ['attr' => $this->walletAttribute]), __($this->successCode), 200, $wallet);

        return $response;
    }

    private function findByEmail($email){
      $user = User::where('email', $email)->first();

      if(!$user){
        return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
      }

      return (new UserResource($user))
            ->additional([
              $this->status => true,
              'code' => __($this->successCode),
              $this->message => __($this->foundMessage, ['attr' => 'user']),
            ], 200);
    }

    public function update(Request $request, $id){
      $user = User::find($id);

      if(!$user){
        return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
      }

      $rules = [
        $this->firstName => $this->isNullableString,
        $this->lastName => $this->isNullableString,
        $this->email => 'nullable|email|max:255|unique:users,email,'.$user->id,
        $this->phoneNumber => $this->isNullableString.'|unique:users,phone_number,'.$user->id
      ];

      $validator =  Validator::make($request->all(), $rules);

      if($validator->fails()){
        return $this->jsonValidationError($validator);
      }

      $unhashedEmailVerificationCode = null;

      if(isset($request->email) && $request->email != $user->email){
        $unhashedEmailVerificationCode = str_shuffle(uniqid().uniqid());

        $emailVerificationCode = Hash::make($unhashedEmailVerificationCode);

        $emailVerifiedAt = null;

        $user->email_verification_code = $emailVerificationCode;
        $user->email_verified_at = $emailVerifiedAt;
      }

      if(isset($request->phone_number) && ($request->phone_number != $user->phone_number)){
        $user->phone_number_verified = false;
        $user->save();

        try {
          $request->merge([
              'status'=> false,
            ]);
          $res = $this->call('PUT', $request, config('businessws.url').'/api/v1/business/'.$request->businessId.'/sms', [
              'content-type' => 'application/json',
              'accept' => 'application/json'
              ]);

        } catch (Throwable $e) {
          Loggable::error($e);
          return $this->jsonResponse($e->getMessage(), __($this->errorCode), 500, [], __('Something went wrong.'));
        }
      }

      $user->fill([
        $this->firstName => $request->input($this->firstName) ? $request->input($this->firstName) : $user->first_name,
        $this->lastName => $request->input($this->lastName) ? $request->input($this->lastName) : $user->last_name,
        $this->email => $request->input($this->email) ? $request->input($this->email) : $user->email,
        $this->phoneNumber => $request->input($this->phoneNumber) ? $request->input($this->phoneNumber) : $user->phone_number,
      ])->save();

      return (new UserResource($user))
            ->additional([
              $this->status => true,
              'code' => __($this->successCode),
              'email_verification_code' => $unhashedEmailVerificationCode,
              $this->message => __($this->updatedMessage, ['attr' => $this->userAttribute]),
            ], 200);
    }


    public function destroy($id){
      $user = User::find($id);

      $old = $user;

      if(!$user){
        return $this->jsonResponse(__($this->notFoundMessage, ['attr' => $this->userAttribute]), __($this->notFoundErrorCode), 404, [], __($this->notFoundError));
      }

      $user->delete();

      return (new UserResource($old))
            ->additional([
              $this->status => true,
              'code' => __($this->successCode),
              $this->message => __($this->deletedMessage, ['attr' => $this->userAttribute]),
            ], 200);
    }
}
