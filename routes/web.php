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

$router->group(['prefix' => 'auth'], function() use ($router) {
    // Login
    $router->post('login', 'AuthController@login');
    // Get logged user info
    $router->get('me', 'AuthController@me');
    // Refresh token
    $router->get('refresh', 'AuthController@refresh');
});
