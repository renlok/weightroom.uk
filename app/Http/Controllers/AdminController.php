<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use Carbon\Carbon;

class AdminController extends Controller
{
	public static function adminCheck()
	{
		if (Auth::user()->user_id != 1) abort(404);
	}

	public function home()
	{
		AdminController::adminCheck();
		return view('admin.index');
	}
	
	public function userStats()
	{
		AdminController::adminCheck();
		$users_with_logs = DB::select('user_id', DB::raw('COUNT(log_id) as log_count'), 'log_date')
			->table('logs')
			->groupBy('user_id')
			->orderBy('log_date', 'desc')
			->get();
		$three_months = Carbon::now()->subMonths(3)->toDateString();
		$active_users = 0;
		foreach ($users_with_logs as $user_data)
		{
			if ($user_data->log_date->gt($three_months))
			{
				$active_users++;
			}
		}
		$all_users = DB::select(DB::raw('COUNT(user_id) as user_count'))->table('users')->value('user_count');
		return view('admin.index');
	}
}
