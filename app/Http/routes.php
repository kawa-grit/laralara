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

    Route::get('', 'HomeController@index');

    Route::group(['domain' => 'dev.back-gammon.tv'], function() {
        Route::get('calc/{xgid}', 'Regular\PositionController@calc');

        Route::get('images/xg/{xgid}', 'Regular\PositionController@images');
        Route::get('download/xg/{xgid}', 'Regular\PositionController@download');

        Route::group(['prefix' => 'record'], function () {
            Route::get('turn/{xgid}', function ($xgid) {
                $xgidObj = new \App\Util\XGID($xgid);
                $action = $xgidObj->action;
                $xgidObj->nextTurn();
                $xgidObj->action = $action;
                return $xgidObj->xgidValue();
            });
            Route::get('dice/{xgid}', function ($xgid) {
                $xgidObj = new \App\Util\XGID($xgid);
                return $xgidObj->action->value;
            });
            Route::get('dice/{xgid}/{action}', function ($xgid, $action) {
                $xgidObj = new \App\Util\XGID($xgid);
                $xgidObj->action = new \App\Util\XGIDAction($action);
                return $xgidObj->xgidValue();
            });
            Route::get('{xgid?}', function ($xgid=NULL) {
                if (isset($xgid)) {
                    new \App\Util\XGID($xgid);
                } else {
                    $xgid = \App\Util\XGID::INIT;
                }
                return view('record', ['xgid'=>$xgid]);
            });
        });
    });

    Route::group(['domain' => 'reg.back-gammon.tv'], function() {
        Route::get('', function () {
            return 'Hello! Domain3';
        });
    });
});
