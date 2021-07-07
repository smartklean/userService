<?php

namespace App\Http\Controllers\Apis\v1;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
use App\Traits\HandlesJsonResponse;

class AccessTokensController extends Controller
{
    use HandlesJsonResponse;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $tokenEndpoint = '/api/v1/user/oauth/token';
    private $errorCode = 'response.codes.error';
    private $successCode = 'response.codes.success';
    private $appDockerInternal = 'app.docker_internal';

    public function authenticate(Request $request){
      $user = User::where('email', $request->input('email'))->first();

      if($user)
        $passwordIsValid = Hash::check($request->input('password'), $user->password);
      else
        $passwordIsValid = false;

      if(!$user || !$passwordIsValid){
        return $this->jsonResponse(__('response.messages.unauthenticated'), __('response.codes.unauthenticated'), 401, [], __('response.errors.unauthenticated'));
      }

      $res = Http::asForm()->post(config($this->appDockerInternal).$this->tokenEndpoint, [
          'grant_type' => 'password',
          'client_id' => $request->header('Client-Public'),
          'client_secret' => $request->header('Client-Secret'),
          'scope' => '',
          'username' => $request->input('email'),
          'password' => $request->input('password')
      ]);

      $response = json_decode($res, true);

      if($res->status() !== 200){
        return $this->jsonResponse($response['message'], __($this->errorCode), 400, [], $response['error']);
      }

      return (new UserResource($user))
            ->additional([
              'status' => true,
              'code' => __($this->successCode),
              'message' => __('response.messages.authenticated'),
              'token' => $response
            ], 200);
    }

    public function revokeToken(Request $request){
      $user = $request->user();

      $tokenRepository = app(TokenRepository::class);
      $refreshTokenRepository = app(RefreshTokenRepository::class);

      foreach($user->tokens as $token){
        $tokenRepository->revokeAccessToken($token->id);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
      }

      return response()->json([
        'status' => true,
        'code' => __($this->successCode),
        'message' => __('response.messages.token_revoked')
      ], 200);
    }

    public function refreshToken(Request $request){
      $res = Http::asForm()->post(config($this->appDockerInternal).$this->tokenEndpoint, [
          'grant_type' => 'refresh_token',
          'refresh_token' => $request->header('Refresh-Token'),
          'client_id' => $request->header('Client-Public'),
          'client_secret' => $request->header('Client-Secret'),
          'scope' => '',
          'expires_at' => 900
      ]);

      $response = json_decode($res, true);

      if($res->status() !== 200){
        return $this->jsonResponse($response['message'], __($this->errorCode), 400, [], $response['error']);
      }

      return response()->json([
        'status' => true,
        'code' => __($this->successCode),
        'message' => __('response.messages.token_reset'),
        'token' => $response
      ], 200);
    }
}
