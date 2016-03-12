<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => 'web'], function () {
    Route::auth();

    Route::get('auth/{provider}/callback', 'Auth\AuthController@handleProviderCallback');
    Route::get('auth/{provider}', 'Auth\AuthController@redirectToProvider');

    Route::get('/home', 'HomeController@index');

    Route::group(['domain' => 'dev.back-gammon.tv'], function() {
        Route::get('', 'Regular\Controller@index');
        Route::get('sss', 'Regular\Controller@show');
        Route::get('aaa', function () {
            return view('welcome');
        });
        Route::get('test/xg', 'Regular\PositionController@test');
        Route::get('images/xg', 'Regular\PositionController@images');
        Route::get('download/xg', 'Regular\PositionController@download');
    });

    Route::group(['domain' => 'reg.back-gammon.tv'], function() {
        Route::get('', function () {
            return 'Hello! Domain3';
        });
    });
});

Route::get('', function () {
    return 'Hello!';
});
