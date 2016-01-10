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
    Route::post('search', 'userController@search')->name('userSearch');
    Route::get('settings', 'userController@getSettings')->name('userSettings');
    Route::post('settings', 'userController@postSettings');
    // follow/unfollow routes
    Route::get('follow/{user_name}/{date?}', 'userController@follow')->name('followUser');
    Route::get('unfollow/{user_name}/{date?}', 'userController@unfollow')->name('unfollowUser');
});

// Log controller
Route::group(['prefix' => 'log', 'middleware' => 'auth'], function () {
    // ajax
    Route::get('{date}/cal/{user_name}', 'LogsController@getAjaxcal')->name('ajaxCal');
    // view log
    Route::get('{date}/view/{user_name?}', 'LogsController@view')->name('viewLog');
    //edit log
    Route::group(['middleware' => 'log.notexists'], function () {
        Route::get('{date}/edit', 'LogsController@getEdit')->name('editLog');
        Route::post('{date}/edit', 'LogsController@postEdit');
    });
    //new log
    Route::group(['middleware' => 'log.exists'], function () {
        Route::get('{date}/new', 'LogsController@getNew')->name('newLog');
        Route::post('{date}/new', 'LogsController@postNew');
    });
    Route::get('{date}/delete', 'LogsController@delete')->name('deleteLog');
    // search logs
    Route::get('search', 'LogsController@getSearch')->name('searchLog');
    Route::post('search', 'LogsController@postSearch');
    // total volume
    Route::get('volume/{from_date?}/{to_date?}', 'LogsController@getVolume')->name('totalVolume');
    Route::post('volume', 'LogsController@postVolume');
    Route::get('{user_name}', 'LogsController@viewUser')->name('viewUser');
});

// Exercise Controller
Route::group(['prefix' => 'exercise', 'middleware' => 'auth'], function () {
    // list exercises
    Route::get('/', 'ExercisesController@getList');
    Route::get('list', 'ExercisesController@getList')->name('listExercises');
    Route::get('{exercise_name}/view/{type?}/{range?}/{force_pr_type?}/', 'ExercisesController@getViewExercise')->name('viewExercise');
    // edit exercise routes
    // can automatically pull the exercise with (App\Exercise $user)
    Route::get('{exercise_name}/edit', 'ExercisesController@getEdit')->name('editExercise');
    Route::post('{exercise_name}/edit', 'ExercisesController@postEdit');
    Route::get('{exercise_name}/history', 'ExercisesController@history')->name('exerciseHistory');
    Route::get('{exercise_name}/history/{from_date}/{to_date}', 'ExercisesController@history')->name('exerciseHistoryRange');
    Route::get('{exercise_name}/volume/{from_date?}/{to_date?}', 'ExercisesController@volume')->name('volume');
    Route::get('compare', 'ExercisesController@getCompareForm')->name('compareExercisesForm');
    Route::get('compare/{reps}/{exercise1}/{exercise2?}/{exercise3?}/{exercise4?}/{exercise5?}', 'ExercisesController@getCompare')->name('compareExercises');
    Route::post('compare', 'ExercisesController@postCompare');
});

// Tools controller
Route::group(['prefix' => 'tools', 'middleware' => 'auth'], function () {
    Route::get('/', 'ToolsController@index')->name('tools');
    Route::get('bodyweight/{range?}', 'ToolsController@bodyweight')->name('bodyweightGraph');
    Route::get('wilks/{range?}', 'ToolsController@wilks')->name('wilksGraph');
    Route::get('sinclair/{range?}', 'ToolsController@sinclair')->name('sinclairGraph');
    Route::get('invites', 'ToolsController@invites')->name('invites');
});

// Misc
//Route::get('/', 'MiscController@index');
Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', 'MiscController@dash')->name('dashboard');
});

Route::get('demo', 'MiscController@demo')->name('demo');

// legal guff
Route::get('help/privacypolicy', 'MiscController@privacyPolicy')->name('privacyPolicy');

Route::get('/', function () {
    return view('welcome');
})->name('home');

// check routes with artisan routes:list
//http://laravel.com/docs/master/controllers#restful-resource-controllers
Route::resource('blog', 'BlogController', ['names' => [
    'create' => 'photo.build'
]]);

Route::group(['middleware' => 'auth'], function () {
    Route::post('comment/{log_id}', 'CommentController@store')->name('saveComment');
});
