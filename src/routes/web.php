<?php

use Illuminate\Http\Request;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function() {
    if(config('app.env') != "production"){
      return response()->json([
        'status' => true,
        'data' => [
          'key' => Illuminate\Support\Str::random(32),
        ],
        'message' => 'Welcome to CashEnvoy!'
      ], 200);
    }

    return response()->json([
      'status' => true,
      'message' => 'Welcome to CashEnvoy!'
    ], 200);
});

$router->get('/health', function() {
    return response()->json([
      'status' => true,
    ], 200);
});

$router->group([
  'prefix' => 'api',
], function() use ($router) {
  /* Version 1 */
  $router->group([
    'prefix' => 'v1'
  ], function() use ($router) {
    $router->group([
      'prefix' => 'user',
    ], function() use ($router) {
      $router->get('/get[/{limit}]', 'Apis\v1\UsersController@fetch');
      $router->put('/{id}/update', 'Apis\v1\UsersController@update');
      $router->post('/create', 'Apis\v1\UsersController@store');
      $router->delete('/{id}/delete', 'Apis\v1\UsersController@destroy');
      $router->post('/authenticate', 'Apis\v1\AccessTokensController@authenticate');
      $router->put('/change/password', 'Apis\v1\UsersController@changePassword');

      $router->group([
        'prefix' => 'token'
      ], function() use ($router) {
        $router->group([
          'middleware' => 'auth:api'
        ], function() use ($router) {
          $router->get('/validate', 'Apis\v1\UsersController@fetchUserInstance');
        });
        $router->post('/revoke', 'Apis\v1\AccessTokensController@revokeToken');
        $router->post('/refresh', 'Apis\v1\AccessTokensController@refreshToken');
      });

      $router->group([
        'prefix' => 'password'
      ], function() use ($router) {
        $router->post('/email', 'Apis\v1\PasswordController@sendPasswordResetEmail');
        $router->post('/reset', 'Apis\v1\PasswordController@resetPassword');
      });

      $router->group([
        'prefix' => 'email'
      ], function() use ($router) {
        $router->post('/resend', 'Apis\v1\VerifyEmailController@resendVerificationCode');
        $router->post('/verify', 'Apis\v1\VerifyEmailController@verifyEmail');
      });
    });
  });
  /* Version 1 */
});
