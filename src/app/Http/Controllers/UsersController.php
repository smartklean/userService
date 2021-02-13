<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    //

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
        ], 409);
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
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        'phone_number' => 'required|string|max:255|unique:users,phone_number,'.$user->id,
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
        ], 409);
      }

      $user->fill([
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'email' => $request->input('email'),
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
