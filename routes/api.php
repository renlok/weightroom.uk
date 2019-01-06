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

Route::prefix('v1')->group(function () {
    // auth routes (password grant)
    Route::post('login/refresh', 'ApiV1Controller@refresh');
    Route::post('login', 'ApiV1Controller@login');
    Route::post('user/register', 'ApiV1Controller@register');
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', 'ApiV1Controller@logout');
        Route::post('log/submit', 'ApiV1Controller@submitLog');
        Route::get('log/{log_date}', 'ApiV1Controller@getLogData');
        Route::get('cal', 'ApiV1Controller@getCalenderData');
        Route::get('userdata', 'ApiV1Controller@getUserData');
    });
});
