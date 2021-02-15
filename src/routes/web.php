<?php

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
  $router->group([
    'prefix' => 'users'
  ], function() use ($router) {
    $router->get('/get[/{limit}]', 'UsersController@fetch');
    $router->get('/{id}/get', 'UsersController@fetchSingle');
    $router->put('/{id}/update', 'UsersController@update');
    $router->post('/create', 'UsersController@store');
    $router->delete('/{id}/delete', 'UsersController@destroy');
  });
});
