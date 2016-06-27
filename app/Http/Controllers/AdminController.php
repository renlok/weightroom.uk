<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use App\Comment;
use App\Log;
use App\Notification;
use Carbon;

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
}
