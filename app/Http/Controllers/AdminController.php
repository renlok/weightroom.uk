<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use Carbon\Carbon;

use App\Console\Commands\ImportFiles;
use App\Admin;

class AdminController extends Controller
{
	public function __construct()
	{
		parent::__construct();
		AdminController::adminCheck();
	}
	
	public static function adminCheck()
	{
		if (Auth::user()->user_id != 1) abort(404);
	}

	public function home()
	{
		return view('admin.index');
	}

	public function getStats()
	{
		$stats = DB::table('global_stats')->orderBy('gstat_date', 'desc')->get();
		return view('admin.stats', compact('stats'));
	}

	public function cronImport()
	{
		$import = new ImportFiles;
		$import->handle();
	}

	public function getSettings()
	{
		$settings = Admin::getSettings();
		return view('admin.settings', compact('settings'));
	}
	
	public function postSettings(Request $request)
	{
		// invites_enabled
		Admin::where('setting_name', 'invites_enabled')->update(['setting_value' => $request->input('invites_enabled')]);
		
		return redirect()
			->route('adminSettings')
			->with(['flash_message' => "Settings updated"]);
	}
}
