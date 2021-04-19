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

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function fetchUserInstance(Request $request){
      return (new UserResource($request->user()))
            ->additional([
              'status' => true,
              'message' => __('response.messages.found', ['attr' => 'user']),
            ], 200);
    }

    public function fetch(Request $request, $limit = null){
      $rules = [
          "params" => "nullable|array",
          "params.*" => "nullable|distinct"
      ];

      $validator = Validator::make($request->all(), $rules);

      if($validator->fails()){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.validation'),
          'data' => [
            'errors' => $validator->getMessageBag()->toArray()
          ]
        ], 400);
      }

      $params = $request->input('params');

      if($params){
        $users = [];

        foreach($params as $param){
          $user = User::where([
            'id' => $param
          ])
          ->orWhere([
            'email' => $param
          ])
          ->orWhere([
            'phone_number' => $param
          ])->first();

          array_push($users, $user);
        }
      }else{
        if(!$limit){
          $users = User::all();
        }
        else{
          $users = User::paginate($limit);
        }
      }


      return UserResource::collection($users)
              ->additional([
                'status' => true,
                'message' => __('response.messages.found_multiple', ['attr' => 'users']),
              ], 200);
    }

    public function store(Request $request){
      $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'password' => 'nullable|string|max:255|confirmed',
        'email' => 'required|string|email|max:255|unique:users',
        'phone_number' => 'required|string|max:255|unique:users',
        'is_developer' => 'nullable|boolean',
      ];

      $validator =  Validator::make($request->all(), $rules);

      if($validator->fails()){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.validation'),
          'data' => [
            'errors' => $validator->getMessageBag()->toArray()
          ]
        ], 400);
      }

      $autoGeneratedPassword = Str::random(16);

      $password = $request->input('password') !== null ? Hash::make($request->input('password')) : Hash::make($autoGeneratedPassword);

      $unhashedEmailVerificationCode = str_shuffle(uniqid().uniqid());

      $emailVerificationCode = $request->input('skip_email_verification') ? null : Hash::make($unhashedEmailVerificationCode);

      $emailVerifiedAt = $request->input('skip_email_verification') ? Carbon::now() : null;

      $user = User::create([
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'email' => $request->input('email'),
        'email_verification_code' => $emailVerificationCode,
        'email_verified_at' => $emailVerifiedAt,
        'password' => $password,
        'phone_number' => $request->input('phone_number'),
        'is_developer' => $request->input('is_developer') ? $request->input('is_developer') : false,
      ]);

      //trigger email notification service

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => __('response.messages.added', ['attr' => 'user']),
              'email_verification_code' => $unhashedEmailVerificationCode
            ], 201);
    }

    public function update(Request $request, $id){
      $user = User::find($id);

      if(!$user){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.not_found', ['attr' => 'user'])
        ], 400);
      }

      $rules = [
        'first_name' => 'nullable|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'email' => 'nullable|string|email|max:255|unique:users,email,'.$user->id,
        'phone_number' => 'nullable|string|max:255|unique:users,phone_number,'.$user->id,
        'is_developer' => 'nullable|boolean',
      ];

      $validator =  Validator::make($request->all(), $rules);

      if($validator->fails()){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.validation'),
          'data' => [
            'errors' => $validator->getMessageBag()->toArray()
          ]
        ], 400);
      }

      $user->fill([
        'first_name' => $request->input('first_name') ? $request->input('first_name') : $user->first_name,
        'last_name' => $request->input('last_name') ? $request->input('last_name') : $user->last_name,
        'email' => $request->input('email') ? $request->input('email') : $user->email,
        'phone_number' => $request->input('phone_number') ? $request->input('phone_number') : $user->phone_number,
        'is_developer' => $request->input('is_developer') ? $request->input('is_developer') : $user->is_developer,
      ])->save();

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => __('response.messages.updated', ['attr' => 'user']),
            ], 200);
    }

    public function destroy($id){
      $user = User::find($id);

      $old = $user;

      if(!$user){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.not_found', ['attr' => 'user']),
        ], 400);
      }

      $user->delete();

      return (new UserResource($old))
            ->additional([
              'status' => true,
              'message' => __('response.messages.deleted', ['attr' => 'user']),
            ], 200);
    }
}
