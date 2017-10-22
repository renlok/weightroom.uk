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
    Route::post('user/register', 'Api\ApiV1Controller@register');
    Route::get('log/{user_name}/{log_date}', 'Api\ApiV1Controller@getLogData');
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:api');
});
