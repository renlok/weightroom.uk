<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use DB;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('isvalid', function($attribute, $value, $parameters, $validator) {
            $table = $parameters[0];
            $column = $parameters[1];
            $count = $parameters[2];
            $date = $parameters[3];

            return DB::table($table)->where($column, $value)->where($count, '>', 0)->where($date, '>', Carbon::now())->count() > 0;
        });
        Validator::extend('existsornull', function($attribute, $value, $parameters, $validator) {
            $table = $parameters[0];
            $column = $parameters[1];
            $where = $parameters[2];
            $where_equals = $parameters[3];

            if ($value == 0 || $value == NULL)
            {
                return true;
            }

            return DB::table($table)->where($column, $value)->where($where, $where_equals)->count() > 0;
        });
        Validator::extend('isurlsafe', function($attribute, $value, $parameters, $validator) {
            if (str_replace(['/', '#', '\\', '?', '&'], '-', $value) == $value)
            {
                return true;
            }

            return false;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
