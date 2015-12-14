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

// User controller
Route::group(['prefix' => 'user'], function () {
    Route::get('login', 'UserController@login');
    Route::post('login/do', 'UserController@login_do');
    Route::get('logout', 'UserController@logout');
    Route::get('register', 'UserController@register');
    Route::get('search', 'UserController@search');
    Route::get('settings', 'UserController@settings');
});

// Authentication Routes...
Route::group(['prefix' => 'auth'], function () {
    Route::get('login', 'Auth\AuthController@getLogin');
    Route::post('login', 'Auth\AuthController@postLogin');
    Route::get('logout', 'Auth\AuthController@getLogout');

    // Registration Routes...
    Route::get('register', 'Auth\AuthController@getRegister');
    Route::post('register', 'Auth\AuthController@postRegister');
});

// Log controller
Route::group(['prefix' => 'log'], function () {
    Route::get('/', 'LogsController@index');
    Route::get('view/{date}', 'LogsController@view');
    Route::get('edit/{date}', 'LogsController@edit');
    Route::get('search', 'LogsController@search');
    Route::get('volume', 'LogsController@volume');
});

// Exercise Controller
Route::group(['prefix' => 'exercise'], function () {
    Route::get('/', 'ExercisesController@index');
    Route::get('list', 'ExercisesController@list');
    Route::get('edit/{id}', 'ExercisesController@edit');
    Route::get('history/{id}', 'ExercisesController@history');
    Route::get('volume/{id}', 'ExercisesController@volume');
});

// Tools controller
Route::group(['prefix' => 'tools'], function () {
    Route::get('/', 'ToolsController@index');
    Route::get('bodyweight', 'ToolsController@bodyweight');
    Route::get('wilks', 'ToolsController@wilks');
    Route::get('sinclair', 'ToolsController@sinclair');
    Route::get('invites', 'ToolsController@invites');
});

// Misc
//Route::get('/', 'MiscController@index');
Route::get('ajax', 'MiscController@ajax');
Route::get('demo', 'MiscController@demo');
Route::get('dash', 'MiscController@dash');

Route::get('/', function () {
    return view('welcome');
});
