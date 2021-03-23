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
    $router->get('/token/validate', ['middleware' => 'auth:api', function(){
      return response()->json([
        'status' => true,
        'message' => 'Token authenticated.'
      ], 200);
    }]);

    $router->group([
      'prefix' => 'users',
      'middleware' => 'auth:api'
    ], function() use ($router) {
      $router->get('/get[/{limit}]', 'Apis\v1\UsersController@fetch');
      $router->get('/{id}/get', 'Apis\v1\UsersController@fetchSingle');
      $router->put('/{id}/update', 'Apis\v1\UsersController@update');
      $router->post('/create', 'Apis\v1\UsersController@store');
      $router->post('/authenticate', 'Apis\v1\UsersController@authenticate');
      $router->post('/token/revoke', 'Apis\v1\UsersController@revokeToken');
      $router->post('/token/reset', 'Apis\v1\UsersController@resetToken');
      $router->delete('/{id}/delete', 'Apis\v1\UsersController@destroy');
    });
  });
  /* Version 1 */
});
