<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Auth;

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

$router->get('/', function () use ($router) {
    echo <<<EOF
    <div style="text-align: center;padding-top: 50px;">
        <h1>{$router->app->version()}</h1>
        <h2>Open <a target="__black" href="/apidocs/index.html">API Document</a> </h2>
    </div>
    EOF;
});


$router->group(['prefix' => 'auth/v1', 'middleware' => 'auth'], function () use ($router) {
    $router->get('me', [
        'as' => 'get.my.profile',
        'uses' => 'AuthController@profile'
    ]);
});


$router->group(['prefix' => 'query/v1', 'middleware' => 'auth'], function () use ($router) {

    $router->get('stats', 'ElasticSearchController@stats');

    $router->get('words', [
        'as' => 'query.v1.words',
        'uses' => 'ElasticSearchController@query'
    ]);
});
