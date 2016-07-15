<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use Carbon\Carbon;

use App\Console\Commands\ImportFiles;

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

	public function getStats()
	{
		AdminController::adminCheck();
		$stats = DB::table('global_stats')->orderBy('gstat_date', 'desc')->get();
		return view('admin.stats', compact('stats'));
	}

	public function cronImport()
	{
		$import = new ImportFiles;
		$import->handle();
	}
}
