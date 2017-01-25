<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Auth;
use View;
use App\Notification;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        if (Auth::check())
        {
            $header_notifications = Notification::where('user_id', Auth::user()->user_id);
            View::share('header_notifications', $header_notifications->get());
            View::share('header_notifications_count', $header_notifications->count());
        }
        else
        {
            View::share('header_notifications', []);
            View::share('header_notifications_count', 0);
        }
    }
}
