<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['anonymous', 'checkuserupdates']], function () {
    Route::get('servers', 'Api\ServerController@getServers'); // ->middleware('throttle:30,1');
    Route::get('servers/stats/realtime',
        'Api\ServerController@getRealtimeServersStats'); //->middleware('throttle:30,1');
});