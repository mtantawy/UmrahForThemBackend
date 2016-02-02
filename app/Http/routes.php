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
    Route::group(['prefix' => 'v1', 'namespace' => 'API\v1'], function () {
        Route::post('register', 'UserController@store');
        Route::post('login', function () {
            return Response::json(Authorizer::issueAccessToken());
        });

        Route::group(['prefix' => '/', 'middleware' => 'oauth'], function () {
            Route::get('/', function () {
                return 'API v1!';
            });
            Route::get('deceased/myrequests', ['as' => 'user.deceased.myrequests', 'uses' => 'DeceasedController@myRequests']);
            Route::patch('deceased/{deceased}/updatestatus/{status}', ['as' => 'deceased.umrah.update', 'uses' => 'DeceasedController@updateStatus']);
            Route::resource('deceased', 'DeceasedController', ['except' => ['create', 'edit']]);
            Route::resource('users', 'UserController', ['except' => ['index', 'create', 'edit', 'store']]);
            Route::resource('umrah', 'UmrahController', ['only' => ['index', 'store', 'update', 'show']]);
            Route::resource('deceased.umrah', 'DeceasedUmrahController');
        });

        // allow guest mode to view deceased with no umrahs
        Route::get('deceased', ['uses' => 'DeceasedController@index']);
    });

    Route::get('/', function () {
        return 'API Home!';
    });
});

Route::get('/', function () {
    return view('welcome');
});
