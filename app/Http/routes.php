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
Route::group(['prefix' => 'user', 'middleware' => 'auth'], function () {
    Route::post('search', 'UserController@search')->name('userSearch');
    Route::get('settings', 'UserController@settings')->name('userSettings');
});

// Log controller
Route::group(['prefix' => 'log', 'middleware' => 'auth'], function () {
    Route::get('/', 'LogsController@index')->name('');
    Route::get('view/{date}', 'LogsController@view')->name('view');
    Route::get('edit/{date}', 'LogsController@edit')->name('edit');
    Route::get('search', 'LogsController@search')->name('searchLog');
    Route::get('volume', 'LogsController@volume')->name('totalVolume');
});

// Exercise Controller
Route::group(['prefix' => 'exercise', 'middleware' => 'auth'], function () {
    Route::get('/', 'ExercisesController@index');
    Route::get('list', 'ExercisesController@list')->name('list');
    Route::get('edit/{id}', 'ExercisesController@edit')->name('edit');
    Route::get('history/{id}', 'ExercisesController@history')->name('history');
    Route::get('volume/{id}', 'ExercisesController@volume')->name('volume');
    Route::get('compare', 'ToolsController@compare')->name('compareExercises');
});

// Tools controller
Route::group(['prefix' => 'tools', 'middleware' => 'auth'], function () {
    Route::get('/', 'ToolsController@index')->name('tools');
    Route::get('bodyweight', 'ToolsController@bodyweight')->name('bodyweightGraph');
    Route::get('wilks', 'ToolsController@wilks')->name('wilksGraph');
    Route::get('sinclair', 'ToolsController@sinclair')->name('sinclairGraph');
    Route::get('invites', 'ToolsController@invites')->name('invites');
});

// Misc
//Route::get('/', 'MiscController@index');
Route::group(['middleware' => 'auth'], function () {
    Route::get('ajax', 'MiscController@ajax')->name('ajax');
    Route::get('dashboard', 'MiscController@dash')->name('dashboard');
});

Route::get('demo', 'MiscController@demo')->name('demo');

// legal guff
Route::get('help/privacypolicy', 'MiscController@privacyPolicy')->name('privacyPolicy');

Route::get('/', function () {
    return view('welcome');
})->name('home');
