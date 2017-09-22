<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
        Route::post('oauth/token', [
            'middleware' => 'password-grant',
            'uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken'
        ]);
        Route::post('oauth/token/refresh', [
            'middleware' => ['web', 'auth', 'password-grant'],
            'uses' => '\Laravel\Passport\Http\Controllers\TransientTokenController@refresh'
        ]);
    }
}
