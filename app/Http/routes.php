<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'api'], function () {
	Route::group(['prefix' => 'v1', 'namespace' => 'API\v1', 'middleware' => 'oauth'], function () {
		Route::get('/', function () {
		    return 'API v1!';
		});
		Route::resource('deceased', 'DeceasedController', ['except' => ['create', 'edit']]);
		Route::resource('users', 'UserController', ['except' => ['index', 'create', 'edit']]);
	});
	Route::post('oauth/access_token', function() {
	    return Response::json(Authorizer::issueAccessToken());
	});
	Route::get('/', function () {
	    return 'API Home!';
	});
});

Route::get('/', function () {
    return view('welcome');
});