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
        Route::post('login', 'UserController@login');

        Route::group(['prefix' => '/', 'middleware' => 'oauth'], function () {

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

    Route::group(['prefix' => 'v2', 'namespace' => 'API\v2'], function () {
        Route::post('register', 'UserController@store');
        Route::post('login', 'UserController@login');
        Route::post('resetpassword', 'UserController@resetPassword');

        // Allow guest mode to search, had to placed above to avoid matching with "resource" controller
        Route::get('umrah/search', ['uses'  =>  'UmrahController@search']);
        Route::get('umrah/death_causes', ['uses'  =>  'UmrahController@deathCauses']);
        Route::get('umrah/doneUmrahs', ['as' => 'user.umrah.doneUmrahs', 'uses' => 'UmrahController@doneUmrahs']);

        Route::group(['prefix' => '/', 'middleware' => 'oauth'], function () {

            Route::get('users/me', 'UserController@show');
            Route::patch('users/me', 'UserController@update');
            Route::patch('users/me/updatepassword', 'UserController@updatePassword');
            // these have to be above the "resource" controller thingy to match requests first.
            Route::get('umrah/myrequests', ['as' => 'user.umrah.myrequests', 'uses' => 'UmrahController@myRequests']);
            Route::get('umrah/performedbyme', ['as' => 'user.umrah.performedbyme', 'uses' => 'UmrahController@performedByMe']);
            Route::patch('umrah/{deceased}/updatestatus/{status}', ['as' => 'deceased.umrah.update', 'uses' => 'UmrahController@updateStatus']);
            Route::resource('umrah', 'UmrahController', ['except'   =>  ['create', 'edit']]);
        });

        // allow guest mode to view deceased with no umrahs
        Route::get('umrah', ['uses' => 'UmrahController@index']);
    });
});

Route::get('stats', ['as' => 'app.stats', 'uses' => 'StatsController@index']);
Route::get('/', 'DashboardController@home');
