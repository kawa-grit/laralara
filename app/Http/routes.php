<?php

Route::group(['middleware' => 'web', 'domain' => 'app.river.xvs.jp'], function() {
    // Ex:aB-a-BD-B---dD--ac-cb---A-:1:-1:1:52:0:0:0:7:10
    Route::get('xgid/{xgid}', function ($xgid) {
        return response()->json(\App\Gnubg::execute($xgid)->toArray());
    });
});

Route::get('/', function () {
    return view('welcome');
});
