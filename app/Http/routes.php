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
Route::group(['prefix' => 'user', 'as' => 'user/'], function () {
    Route::get('login', 'AuthController@login')->name('login');
    Route::get('logout', 'AuthController@logout')->name('logout');
    Route::get('register', 'AuthController@register')->name('register');
    // post pages
    Route::post('login', 'AuthController@login_do')->name('login');
    Route::post('register', 'AuthController@register_do')->name('register');
});

Route::group(['prefix' => 'user', 'middleware' => 'auth', 'as' => 'user/'], function () {
    Route::post('search', 'UserController@search')->name('search');
    Route::get('settings', 'UserController@settings')->name('settings');
});

// Log controller
Route::group(['prefix' => 'log', 'middleware' => 'auth', 'as' => 'log/'], function () {
    Route::get('/', 'LogsController@index')->name('');
    Route::get('view/{date}', 'LogsController@view')->name('view');
    Route::get('edit/{date}', 'LogsController@edit')->name('edit');
    Route::get('search', 'LogsController@search')->name('search');
    Route::get('volume', 'LogsController@volume')->name('volume');
});

// Exercise Controller
Route::group(['prefix' => 'exercise', 'middleware' => 'auth', 'as' => 'exercise/'], function () {
    Route::get('/', 'ExercisesController@index');
    Route::get('list', 'ExercisesController@list')->name('list');
    Route::get('edit/{id}', 'ExercisesController@edit')->name('edit');
    Route::get('history/{id}', 'ExercisesController@history')->name('history');
    Route::get('volume/{id}', 'ExercisesController@volume')->name('volume');
});

// Tools controller
Route::group(['prefix' => 'tools', 'middleware' => 'auth', 'as' => 'tools/'], function () {
    Route::get('/', 'ToolsController@index');
    Route::get('bodyweight', 'ToolsController@bodyweight')->name('bodyweight');
    Route::get('wilks', 'ToolsController@wilks')->name('wilks');
    Route::get('sinclair', 'ToolsController@sinclair')->name('sinclair');
    Route::get('invites', 'ToolsController@invites')->name('invites');
});

// Misc
//Route::get('/', 'MiscController@index');
Route::group(['middleware' => 'auth'], function () {
    Route::get('ajax', 'MiscController@ajax')->name('ajax');
    Route::get('dashboard', 'MiscController@dash')->name('dashboard');
});

Route::get('demo', 'MiscController@demo')->name('demo');

Route::get('/', function () {
    return view('welcome');
});
