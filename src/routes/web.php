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
          'key' => 'base64:'.base64_encode(Illuminate\Support\Str::random(32)),
        ],
        'message' => 'Welcome to Sample Application!'
      ], 200);
    }

    return response()->json([
      'status' => true,
      'message' => 'Hooray welcome onboard!'
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
      $router->group([
        'prefix' => 'password'
      ], function() use ($router) {
        $router->post('/verify', 'Apis\v1\PasswordController@verifyPassword');
        $router->group([
          'middleware' => 'auth:api'
        ], function() use ($router) {
          $router->put('/', 'Apis\v1\PasswordController@changePassword');
        });
        $router->post('/email', 'Apis\v1\PasswordController@sendPasswordResetEmail');
        $router->post('/reset', 'Apis\v1\PasswordController@resetPassword');
      });

      $router->get('/', 'Apis\v1\UsersController@fetch');
      $router->get('/{id}', 'Apis\v1\UsersController@fetchSingle');
      $router->get('/wallet/{userId}', 'Apis\v1\UsersController@fetchUserWallet');
      $router->put('/{id}', 'Apis\v1\UsersController@update');
      $router->post('/', 'Apis\v1\UsersController@store');
      $router->delete('/{id}', 'Apis\v1\UsersController@destroy');
      $router->post('/authenticate', 'Apis\v1\AccessTokensController@authenticate');

      $router->group([
        'prefix' => 'token'
      ], function() use ($router) {
        $router->group([
          'middleware' => 'auth:api'
        ], function() use ($router) {
          $router->get('/validate', 'Apis\v1\UsersController@fetchUserInstance');
          $router->post('/revoke', 'Apis\v1\AccessTokensController@revokeToken');
        });
        $router->post('/refresh', 'Apis\v1\AccessTokensController@refreshToken');
      });
      
    });
  });
  /* Version 1 */
});
