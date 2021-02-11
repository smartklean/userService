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
      'message' => 'Hello World!'
    ]);
});

$router->group([
  'prefix' => 'api',
], function() use ($router) {
  $router->group([
    'prefix' => 'users'
  ], function() use ($router) {
    $router->post('/create', 'UsersController@create');
  });
});
