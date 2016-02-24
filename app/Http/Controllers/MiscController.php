<?php

namespace App\Http\Controllers;

use Auth;
use App\Log;
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
		$logs = Log::whereIn('user_id', User_follow::where('user_id', Auth::user()->user_id)->lists('follow_user_id'))->orderBy('log_date', 'desc')->paginate(50);
		return view('dash', compact('logs'));
	}

	public function dashAll()
	{
		$logs = Log::orderBy('log_date', 'desc')->paginate(50);
		return view('dash', compact('logs'));
	}
}
