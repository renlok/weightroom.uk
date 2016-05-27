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

	public function privacyPolicy()
	{
		return view('help.privacypolicy');
	}

	public function dash()
	{
		$followed_users = User_follow::where('user_id', Auth::user()->user_id)->lists('follow_user_id');
		$random = false;
		if ($followed_users == null || $followed_users->count() == 0)
		{
			$followed_users = Cache::remember('random_users_dash', 360, function()
			{
			    return User::select(DB::raw('DISTINCT(users.user_id)'))->join('logs', 'users.user_id', '=', 'logs.user_id')->orderBy(\DB::raw('RAND()'))->take(10)->lists('users.user_id');
			});
			$random = true;
		}
		$logs = Log::whereIn('user_id', $followed_users)->orderBy('log_date', 'desc')->paginate(50);
		return view('dash', compact('logs', 'random'));
	}

	public function dashAll()
	{
		$logs = Log::orderBy('log_date', 'desc')->paginate(50);
		$random = false;
		return view('dash', compact('logs', 'random'));
	}
}
