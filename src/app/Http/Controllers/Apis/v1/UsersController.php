<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
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

    public function fetch($limit = null){
      if(!$limit)
        $users = User::all();
      else
        $users = User::paginate($limit);

      return UserResource::collection($users)
              ->additional([
                'status' => true,
                'message' => 'Success'
              ], 200);
    }

    public function fetchSingle($id){
      $user = User::find($id);

      if(!$user){
        return response()->json([
          'status' => false,
          'message' => 'User could not be found'
        ], 400);
      }

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => 'User added successfully'
            ], 200);
    }

    public function store(Request $request){
      $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'password' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'phone_number' => 'required|string|max:255|unique:users',
        'is_developer' => 'nullable|boolean',
      ];

      $validator =  Validator::make($request->all(), $rules);

      if($validator->fails()){
        return response()->json([
          'status' => false,
          'message' => 'Validation error occured.',
          'data' => [
            'errors' => $validator->getMessageBag()->toArray()
          ]
        ], 400);
      }

      $user = User::create([
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
        'phone_number' => $request->input('phone_number'),
        'is_developer' => $request->input('is_developer') !== null ? true : false,
      ]);

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => 'User added successfully'
            ], 201);
    }

    public function authenticate(Request $request){
      $rules = [
        'password' => 'required|string|max:255',
        'email' => 'required|string|email|max:255'
      ];

      $validator =  Validator::make($request->all(), $rules);

      if($validator->fails()){
        return response()->json([
          'status' => false,
          'message' => 'Validation error occured.',
          'data' => [
            'errors' => $validator->getMessageBag()->toArray()
          ]
        ], 400);
      }

      $user = User::where('email', $request->input('email'))->first();

      if($user)
        $passwordIsValid = Hash::check($request->input('password'), $user->password);
      else
        $passwordIsValid = false;

      if(!$user || !$passwordIsValid){
        return response()->json([
          'status' => false,
          'message' => 'Unauthenticated.'
        ], 401);
      }

      $tokenGen = $user->createToken('Personal Access Token');

      $token = $tokenGen->token;

      if($request->remember) $token->expires_at = Carbon::now()->addWeeks(1);

      $token->save();

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => 'Authenticated.',
              'token' => $tokenGen->accessToken
            ], 200);
    }

    public function update(Request $request, $id){
      $user = User::find($id);

      if(!$user){
        return response()->json([
          'status' => false,
          'message' => 'User could not be found'
        ], 400);
      }

      $rules = [
        'first_name' => 'nullable|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'password' => 'nullable|string|max:255',
        'email' => 'nullable|string|email|max:255|unique:users,email,'.$user->id,
        'phone_number' => 'nullable|string|max:255|unique:users,phone_number,'.$user->id,
        'is_developer' => 'nullable|boolean',
      ];

      $validator =  Validator::make($request->all(), $rules);

      if($validator->fails()){
        return response()->json([
          'status' => false,
          'message' => 'Validation error occured.',
          'data' => [
            'errors' => $validator->getMessageBag()->toArray()
          ]
        ], 400);
      }

      $user->fill([
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
        'phone_number' => $request->input('phone_number'),
        'is_developer' => $request->input('is_developer') !== null ? true : false,
      ])->save();

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => 'User updated successfully'
            ], 200);
    }

    public function destroy($id){
      $user = User::find($id);

      $old = $user;

      if(!$user){
        return response()->json([
          'status' => false,
          'message' => 'User could not be found'
        ], 400);
      }

      $user->delete();

      return (new UserResource($old))
            ->additional([
              'status' => true,
              'message' => 'User deleted successfully'
            ], 200);
    }
}
