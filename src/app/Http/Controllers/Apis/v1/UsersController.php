<?php

namespace App\Http\Controllers\Apis\v1;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

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
                'message' => __('response.messages.found_multiple', ['attr' => 'users']),
              ], 200);
    }

    public function fetchSingle($id){
      $user = User::find($id);

      if(!$user){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.not_found', ['attr' => 'user']),
        ], 400);
      }

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => __('response.messages.found', ['attr' => 'user']),
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
          'error' => __('response.errors.request'),
          'message' => __('response.messages.validation'),
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
        'is_developer' => $request->input('is_developer') !== null ? $request->input('is_developer') : false,
      ]);

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => __('response.messages.added', ['attr' => 'user']),
            ], 201);
    }

    public function authenticate(Request $request){
      $rules = [
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|max:255',
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

      $user = User::where('email', $request->input('email'))->first();

      if($user)
        $passwordIsValid = Hash::check($request->input('password'), $user->password);
      else
        $passwordIsValid = false;

      if(!$user || !$passwordIsValid){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.unauthenticated'),
          'messsage' => __('response.messages.unauthenticated'),
        ], 401);
      }

      $res = Http::asForm()->post(config('app.docker_internal').'/oauth/token', [
          'grant_type' => 'password',
          'client_id' => $request->header('Client-Public'),
          'client_secret' => $request->header('Client-Secret'),
          'scope' => '',
          'username' => $request->input('email'),
          'password' => $request->input('password')
      ]);

      $response = json_decode($res, true);

      if($res->status() !== 200){
        return response()->json([
          'status' => false,
          'error' => $response['error'],
          'message' => $response['message']
        ], 400);
      }

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'message' => __('response.messages.authenticated'),
              'token' => $response
            ], 200);
    }

    public function revokeToken(Request $request){
      $user = $request->user();

      if(!$user){
        return response()->json([
          'status' => false,
          'error' => __('response.errors.request'),
          'message' => __('response.messages.not_found', ['attr' => 'user'])
        ], 400);
      }

      $tokenRepository = app(TokenRepository::class);
      $refreshTokenRepository = app(RefreshTokenRepository::class);

      foreach($user->tokens as $token){
        $tokenRepository->revokeAccessToken($token->id);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
      }

      return response()->json([
        'status' => true,
        'message' => __('response.messages.token_revoked')
      ], 200);
    }

    public function resetToken(Request $request){
      $res = Http::asForm()->post(config('app.docker_internal').'/oauth/token', [
          'grant_type' => 'refresh_token',
          'refresh_token' => $request->header('Refresh-Token'),
          'client_id' => $request->header('Client-Public'),
          'client_secret' => $request->header('Client-Secret'),
          'scope' => '',
          'expires_at' => 900
      ]);

      $response = json_decode($res, true);

      if($res->status() !== 200){
        return response()->json([
          'status' => false,
          'error' => $response['error'],
          'message' => $response['message']
        ], 400);
      }

      return response()->json([
        'status' => true,
        'message' => __('response.messages.token_reset'),
        'token' => $response
      ], 200);
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
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'email' => $request->input('email'),
        'phone_number' => $request->input('phone_number'),
        'is_developer' => $request->input('is_developer') !== null ? $request->input('is_developer') : false,
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
