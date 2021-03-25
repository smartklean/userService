<?php

namespace App\Http\Controllers\Apis\v1;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class AccessTokenController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
}
