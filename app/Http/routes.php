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

// Authentication routes...
Route::get('login', 'LoginController@getLogin')->name('login');
Route::post('login', 'LoginController@postLogin');
Route::get('logout', 'LoginController@getLogout')->name('logout');

// Registration routes...
Route::get('register', 'LoginController@getRegister')->name('register');
Route::post('register', 'LoginController@postRegister');

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail')->name('emailPassword');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset')->name('passwordReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

// User controller
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
})->name('home');
