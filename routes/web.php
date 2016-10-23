<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

/*
 * Server
 */
Route::group(['middleware' => ['anonymous', 'checkuserupdates']], function () {
    Route::get('/', 'Web\ServerController@getServersList')->name('serversList');

    Route::get('/servers/create', 'Web\ServerController@getServerCreate')->name('serverCreate');
    Route::post('/servers/create', 'Web\ServerController@postServerCreate');

    Route::get('/servers/{serverId}/edit', 'Web\ServerController@getServerEdit')->name('serverEdit');
    Route::post('/servers/{serverId}/edit', 'Web\ServerController@postServerEdit');
});

/*
 * User
 */

Route::get('/my-account', 'Web\UserController@getAccount')->name('account');
Route::post('/my-account', 'Web\UserController@postAccount');

/*
 * Auth
 */
Route::get('/login', 'Web\AuthController@getLogin')->name('login');
Route::post('/login', 'Web\AuthController@postLogin');

Route::get('/logout/{logoutToken}', 'Web\AuthController@getLogout')->name('logout');
