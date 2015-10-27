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

Route::group(['domain' => 'api.umrahforthem.app'], function () {
	Route::group(['prefix' => 'v1', 'namespace' => 'API/v1'], function () {
		Route::get('/', function () {
		    return 'API v1!';
		});
		
	});
	Route::get('/', function () {
	    return 'API Home!';
	});
});

Route::get('/', function () {
    return view('welcome');
});