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
    Route::get('password/reset/{token}', 'Auth\PasswordController@getReset')->name('password.reset');
    Route::post('password/reset', 'Auth\PasswordController@reset');
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
        Route::get('list/following', 'UserController@getFollowingList')->name('followingList');
        Route::get('list/followers', 'UserController@getFollowersList')->name('followersList');
        Route::get('acceptfollow/{user_name}', 'UserController@getAcceptUserFollow')->name('acceptUserFollow');
        Route::get('follow/{user_name}/{date?}', 'UserController@follow')->name('followUser');
        Route::get('unfollow/{user_name}/{date?}', 'UserController@unfollow')->name('unfollowUser');
        Route::get('notifications/clear', 'UserController@clearNotifications')->name('clearNotifications');
        Route::get('notification/{note_id}/clear', 'UserController@clearNotification')->name('clearNotification');
        // subscription routes
        Route::get('premium', 'SubscriptionController@getPremium')->name('userPremium');
        Route::post('premium', 'SubscriptionController@postPremium');
        Route::get('premium/cancel', 'SubscriptionController@getCancelPremium')->name('userCancelPremium');
        Route::get('premium/resume', 'SubscriptionController@getResumePremium')->name('userResumePremium');
        // seller setup
        Route::get('seller-setup', 'TemplateController@getSetupPayAccount')->name('setupPayAccount');
        Route::post('seller-setup', 'TemplateController@postSetupPayAccount');
        Route::get('seller-setup/bank', 'TemplateController@getSetupPayAccountBank')->name('setupPayAccountBank');
        Route::post('seller-setup/bank', 'TemplateController@postSetupPayAccountBank');
    });
});

// stripe failed payment route
Route::post(
    'stripe/webhook',
    '\Laravel\Cashier\Http\Controllers\WebhookController@handleWebhook'
);

// Log controller
Route::group(['middleware' => 'auth'], function () {
    Route::get('track', 'LogsController@getTrack')->name('track');
});
Route::group(['prefix' => 'log'], function () {
    // ajax
    Route::get('{short_date}/cal/{user_name}', 'LogsController@getAjaxcal')->name('ajaxCal');
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
    // groups
    Route::get('groups', 'ExercisesController@getExerciseGroups')->name('exerciseGroups');
    Route::post('groups/add', 'ExercisesController@postNewGroup')->name('addExerciseGroup');
    Route::get('groups/delete/{group_id}', 'ExercisesController@getDeleteGroup')->name('deleteExerciseGroup');
    Route::post('groups/exercise/add', 'ExercisesController@postAddToGroup')->name('addToExerciseGroup');
    Route::post('groups/exercise/delete', 'ExercisesController@postDeleteFromGroup')->name('deleteFromExerciseGroup');
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

// export
Route::group(['middleware' => 'auth', 'prefix' => 'export'], function () {
    Route::get('/', 'ImportController@exportForm')->name('export');
    Route::post('process', 'ImportController@processExport')->name('processExport');
    Route::get('download', 'ImportController@downloadExport')->name('downloadExport');
});

// templates
Route::group(['middleware' => 'auth', 'prefix' => 'templates'], function () {
    Route::get('/', 'TemplateController@home')->name('templatesHome');
    Route::get('list/{template_type}', 'TemplateController@viewType')->name('templatesTypeList');
    Route::get('view/{template_id}', 'TemplateController@viewTemplate')->name('viewTemplate');
    Route::get('build/active', 'TemplateController@buildTemplateActive')->name('buildActiveTemplate');
    Route::post('build/direct', 'TemplateController@buildTemplateDirect')->name('buildTemplate');
    Route::post('save/{active?}', 'TemplateController@saveTemplate')->name('saveTemplate');
    // template tools
    Route::get('add', 'TemplateController@getAddTemplate')->name('addTemplate');
    Route::post('add', 'TemplateController@postAddTemplate');
    Route::group(['middleware' => 'own.template'], function () {
        Route::get('edit/{template_id}', 'TemplateController@getEditTemplate')->name('editTemplate');
        Route::post('edit/{template_id}', 'TemplateController@postEditTemplate');
        Route::get('delete/{template_id}', 'TemplateController@getDeleteTemplate')->name('deleteTemplate');
    });
    // active templates
    Route::match(['get', 'post'], 'active/{template_id}', 'TemplateController@setActiveTemplate')->name('setActiveTemplate');
    // template payments
    Route::get('purchase/{template_id}', 'TemplateController@getTemplateSaleProcess')->name('templateSaleProcess');
    Route::post('purchase/{template_id}', 'TemplateController@postTemplateSaleProcess');
});

// admin
Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function () {
    Route::get('/', 'AdminController@home')->name('adminHome');
    Route::get('stats', 'AdminController@getStats')->name('adminStats');
    Route::get('logs/{raw?}/{log?}', 'AdminController@getViewLogs')->name('adminViewLogs');
    Route::get('delete-log/{log}', 'AdminController@cleanLogFile')->name('adminDeleteLog');
    Route::get('settings', 'AdminController@getSettings')->name('adminSettings');
    Route::post('settings', 'AdminController@postSettings');

    Route::get('users', 'AdminController@getListUsers')->name('adminListUsers');

    Route::get('template', 'AdminController@getListTemplates')->name('adminListTemplates');
    Route::get('template/edit/{template_id}', 'AdminController@getEditTemplate')->name('adminEditTemplate');
    Route::post('template/edit/{template_id}', 'AdminController@postEditTemplate');
    Route::get('template/add', 'AdminController@getAddTemplate')->name('adminAddTemplate');
    Route::post('template/add', 'AdminController@postAddTemplate');
    Route::get('template/delete/{template_id}', 'AdminController@getDeleteTemplate')->name('adminDeleteTemplate');

    Route::get('blog', 'BlogController@getListBlogPosts')->name('adminListBlogPosts');
    Route::get('blog/edit/{post_id}', 'BlogController@getEditBlogPost')->name('adminEditBlogPost');
    Route::post('blog/edit/{post_id}', 'BlogController@postEditBlogPost');
    Route::get('blog/add', 'BlogController@getAddBlogPost')->name('adminAddBlogPost');
    Route::post('blog/add', 'BlogController@postAddBlogPost');
    Route::get('blog/delete/{post_id}', 'BlogController@getDeleteBlogPost')->name('adminDeleteBlogPost');

    Route::get('cron/import', 'AdminController@cronImport')->name('cronImport');
    Route::get('stats/force', 'AdminController@forceStats')->name('forceStats');
    Route::get('cleannames', 'AdminController@forceCleanNames')->name('forceCleanNames');
    Route::get('clean', 'AdminController@cleanJunk')->name('cleanJunk');
    Route::get('exercise/rebuild/{exercise_id}', 'AdminController@forceRebuildExercisePRTable')->name('adminRebuildExercisePRTable');
});

// Misc
//Route::get('/', 'MiscController@index');
Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', 'MiscController@dash')->name('dashboard');
    Route::get('dash', 'MiscController@dash');
    Route::get('dashboard/all', 'MiscController@dashAll')->name('dashboardAll');
    Route::get('dash/all', 'MiscController@dashAll');
    Route::get('help/contact', 'MiscController@getContactUs')->name('contactUs');
    Route::post('help/contact', 'MiscController@postContactUs');
});

Route::get('demo', 'MiscController@demo')->name('demo');
Route::get('plans', 'MiscController@plans')->name('plans');
Route::get('faq', 'MiscController@faq')->name('faq');
Route::get('/', 'MiscController@landing')->name('home');

// legal guff
Route::get('help/privacy', 'MiscController@privacyPolicy')->name('privacyPolicy');
Route::get('help/terms', 'MiscController@termsOfService')->name('termsOfService');

Route::group(['prefix' => 'comment', 'middleware' => 'auth'], function () {
    Route::get('{comment_id}/delete', 'CommentController@delete')->name('deleteComment');
    Route::post('log/{object_id}', 'CommentController@storeLogComment')->name('storeLogComment');
    Route::post('blog/{object_id}', 'CommentController@storeBlogComment')->name('storeBlogComment');
});

Route::group(['prefix' => 'blog'], function () {
    Route::get('/', 'BlogController@viewBlog')->name('viewBlog');
    Route::get('{url}', 'BlogController@viewBlogPost')->name('viewBlogPost');
});

Route::get('test', function () {
    return view('landing_new');
})->name('test');
