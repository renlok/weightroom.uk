<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'guest'], function () {
    // Authentication routes...
    Route::get('login', 'LoginController@getLogin')->name('login');
    Route::post('login', 'LoginController@postLogin');

    // Registration routes...
    Route::get('register', 'LoginController@getRegister')->name('register');
    Route::post('register', 'LoginController@postRegister');

    // Password reset link request routes...
    Route::get('password/email', 'Auth\PasswordController@getEmail')->name('emailPassword');
    Route::post('password/email', 'Auth\PasswordController@postEmail');

    // Password reset routes...
    Route::get('password/reset/{token}', 'Auth\PasswordController@getReset')->name('passwordReset');
    Route::post('password/reset', 'Auth\PasswordController@postReset');
});
Route::get('logout', 'LoginController@getLogout')->name('logout');

// User controller
Route::group(['prefix' => 'user'], function () {
    Route::post('search', 'UserController@search')->name('userSearch');

    Route::group(['middleware' => 'auth'], function () {
        // user settings
        Route::get('settings', 'UserController@getSettings')->name('userSettings');
        Route::post('settings', 'UserController@postSettings');
        // follow/unfollow routes
        Route::get('follow/{user_name}/{date?}', 'UserController@follow')->name('followUser');
        Route::get('unfollow/{user_name}/{date?}', 'UserController@unfollow')->name('unfollowUser');
        Route::get('notifications/clear', 'UserController@clearNotifications')->name('clearNotifications');
    });
});

// Log controller
Route::group(['middleware' => 'auth'], function () {
  Route::get('track', 'LogsController@getTrack')->name('track');
});
Route::group(['prefix' => 'log'], function () {
    // ajax
    Route::get('{date}/cal/{user_name}', 'LogsController@getAjaxcal')->name('ajaxCal');
    // view log
    Route::get('{date}/view/{user_name?}', 'LogsController@view')->name('viewLog');

    // must be logged in to see
    Route::group(['middleware' => 'auth'], function () {
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
        Route::get('volume/{from_date?}/{to_date?}/{n?}', 'LogsController@getVolume')->name('totalVolume');
        Route::post('volume', 'LogsController@postVolume');
    });
    Route::get('{user_name}', 'LogsController@viewUser')->name('viewUser');
});

// Exercise Controller
Route::group(['prefix' => 'exercise', 'middleware' => 'auth'], function () {
    // list exercises
    Route::get('/', 'ExercisesController@getList');
    Route::get('list', 'ExercisesController@getList')->name('listExercises');
    Route::get('{exercise_name}/view/prhistory/', 'ExercisesController@getViewExercisePRHistory')->name('viewExercisePRHistory');
    Route::get('{exercise_name}/view/{type?}/{range?}/{force_pr_type?}/', 'ExercisesController@getViewExercise')->name('viewExercise');
    // edit exercise routes
    Route::get('{exercise_name}/edit', 'ExercisesController@getEdit')->name('editExercise');
    Route::post('{exercise_name}/edit', 'ExercisesController@postEdit');
    Route::post('{exercise_name}/editname', 'ExercisesController@postEditName')->name('editExerciseName');

    Route::post('{exercise_name}/goal/update', 'GoalController@postUpdateExerciseGoals')->name('updateExerciseGoals');
    // history
    Route::get('{exercise_name}/history/{from_date?}/{to_date?}', 'ExercisesController@history')->name('exerciseHistory');
    // volume
    Route::get('{exercise_name}/volume', 'ExercisesController@volume')->name('volume');
    // compare
    Route::get('compare', 'ExercisesController@getCompareForm')->name('compareExercisesForm');
    Route::get('compare/{reps}/{exercise1}/{exercise2?}/{exercise3?}/{exercise4?}/{exercise5?}', 'ExercisesController@getCompare')->name('compareExercises');
    Route::post('compare', 'ExercisesController@postCompare');
});

// Tools controller
Route::group(['prefix' => 'tools'], function () {
    Route::get('/', 'ToolsController@index')->name('tools');

    // user only tools
    Route::group(['middleware' => 'auth'], function () {
        Route::get('bodyweight/{range?}', 'ToolsController@bodyweight')->name('bodyweightGraph');
        Route::get('invites', 'ToolsController@invites')->name('invites');
        // PL tools
        Route::get('wilks/{range?}', 'ToolsController@wilks')->name('wilksGraph');
        // WL tools
        Route::get('sinclair/{range?}', 'ToolsController@sinclair')->name('sinclairGraph');
        // goals
        Route::get('goals', 'GoalController@getGlobalGoals')->name('globalGoals');
        Route::post('goal/new', 'GoalController@postNewGoal')->name('newGoal');
        Route::post('goal/update', 'GoalController@postUpdateGoal')->name('updateGoal');
        Route::post('goal/delete', 'GoalController@postDeleteGoal')->name('deleteGoal');
        // reports
        Route::get('reports', 'LogsController@getReports')->name('viewReports');
        Route::post('reports', 'LogsController@ajaxGetReport')->name('ajaxPullReports');
    });
    // guest friendly tools
    Route::get('rpeestimator', 'ToolsController@RPECalculator')->name('rpeestimator');
    Route::get('rmcalculator', 'ToolsController@RMcalculator')->name('rmcalculator');
    Route::get('wlratios', 'ToolsController@idealWLRatios')->name('wlratios');
});

// import
Route::group(['middleware' => 'auth', 'prefix' => 'import'], function () {
    Route::get('/', 'ImportController@importForm')->name('import');
    Route::post('/', 'ImportController@import');
    Route::post('store', 'ImportController@storeImport')->name('storeImport');
    Route::get('success', 'ImportController@importSuccess')->name('successImport');
});

// templates
Route::group(['middleware' => 'auth', 'prefix' => 'templates'], function () {
    Route::get('/', 'TemplateController@home')->name('templatesHome');
    Route::get('view/{template_id}', 'TemplateController@viewTemplate')->name('viewTemplate');
    Route::post('build', 'TemplateController@postBuildTemplate')->name('buildTemplate');
});

// admin
Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function () {
    Route::get('/', 'AdminController@home')->name('adminHome');
    Route::get('stats', 'AdminController@getStats')->name('adminStats');
    Route::get('settings', 'AdminController@getSettings')->name('adminSettings');
    Route::post('settings', 'AdminController@postSettings');

    Route::get('template', 'AdminController@getListTemplates')->name('adminListTemplates');
    Route::get('template/edit/{template_id}', 'AdminController@getEditTemplate')->name('adminEditTemplate');
    Route::post('template/edit/{template_id}', 'AdminController@postEditTemplate');
    Route::get('template/add', 'AdminController@getAddTemplate')->name('adminAddTemplate');
    Route::post('template/add', 'AdminController@postAddTemplate');
    Route::get('template/delete/{template_id}', 'AdminController@getDeleteTemplate')->name('adminDeleteTemplate');

    Route::get('cron/import', 'AdminController@cronImport')->name('cronImport');
    Route::get('stats/force', 'AdminController@forceStats')->name('forceStats');
    Route::get('exercise/rebuild/{exercise_id}', 'AdminController@forceRebuildExercisePRTable')->name('adminRebuildExercisePRTable');
});

// Misc
//Route::get('/', 'MiscController@index');
Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', 'MiscController@dash')->name('dashboard');
    Route::get('dashboard/all', 'MiscController@dashAll')->name('dashboardAll');
});

Route::get('demo', 'MiscController@demo')->name('demo');
Route::get('plans', 'MiscController@plans')->name('plans');
Route::get('faq', 'MiscController@faq')->name('faq');
Route::get('/', 'MiscController@landing')->name('home');

// legal guff
Route::get('help/privacypolicy', 'MiscController@privacyPolicy')->name('privacyPolicy');

Route::group(['prefix' => 'comment', 'middleware' => 'auth'], function () {
    Route::get('{comment_id}/delete', 'CommentController@delete')->name('deleteComment');
    Route::post('{log_id}', 'CommentController@store')->name('saveComment');
});

// check routes with artisan routes:list
//http://laravel.com/docs/master/controllers#restful-resource-controllers
Route::resource('blog', 'BlogController', ['names' => [
    'create' => 'photo.build'
]]);

Route::get('test', function () {
    return view('landing_new');
})->name('test');
