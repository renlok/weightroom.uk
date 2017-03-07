<?php

namespace App\Http\Controllers;

use Auth;
use Cache;
use DB;
use App\Log;
use App\User;
use App\User_follow;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function demo()
    {
        return view('demo');
    }

    public function plans()
    {
        return view('plans');
    }

    public function faq()
    {
        return view('help.faq');
    }

    public function privacyPolicy()
    {
        return view('help.privacypolicy');
    }

    public function dash()
    {
        $followed_users = User_follow::where('user_id', Auth::user()->user_id)->pluck('follow_user_id');
        $random = false;
        if ($followed_users == null || $followed_users->count() == 0)
        {
            $followed_users = Cache::remember('random_users_dash', 360, function()
            {
                return User::select(DB::raw('DISTINCT(users.user_id)'))->join('logs', 'users.user_id', '=', 'logs.user_id')->orderBy(\DB::raw('RAND()'))->take(10)->pluck('users.user_id');
            });
            $random = true;
            $follow_count = 0;
        }
        else
        {
            $follow_count = $followed_users->count();
        }
        $logs = Log::whereIn('user_id', $followed_users)->whereRaw("TRIM(log_text) != ''")->orderBy('log_date', 'desc')->paginate(50);
        return view('dash', compact('logs', 'random', 'follow_count'));
    }

    public function dashAll()
    {
        $logs = Log::orderBy('log_date', 'desc')->whereRaw("TRIM(log_text) != ''")->paginate(50);
        $random = false;
        $follow_count = 0;
        return view('dash', compact('logs', 'random', 'follow_count'));
    }
}
