<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot()
    {
        // set standard route patterns
        Route::pattern('id', '[0-9]+');
        Route::pattern('template_id', '[0-9]+');
        Route::pattern('comment_id', '[0-9]+');
        Route::pattern('log_id', '[0-9]+');
        Route::pattern('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
        Route::pattern('from_date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
        Route::pattern('to_date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        Route::group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
        });
    }
}
