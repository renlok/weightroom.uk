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
		$cron_count = DB::table('import_data')->select(DB::raw('COUNT(import_id) as cron_count'))->value('cron_count');
		return view('admin.index', compact('cron_count'));
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

	public function getAddTemplate()
	{
		return view('admin.editLogTemplate');
	}

	public function postAddTemplate(Request $request)
	{
		// TODO: validate inputs
		// save the template
		$template = new \App\Template;
		$template->template_name = $request->input('template_name');
		$template->template_description = $request->input('template_description');
		$template->template_type = $request->input('template_type');
		$template->save();
		$template_id = $template->template_id;
		for ($i = 0; $i < count($request->input('log_name')); $i++)
		{
			// save the log
			$log = new \App\Template_log;
			$log->template_id = $template_id;
			$log->template_log_name = $request->input('log_name')[$i];
			$log->template_log_week = $request->input('log_week')[$i];
			$log->template_log_day = $request->input('log_day')[$i];
			$log->has_fixed_values = true;
			$log->save();
			$log_id = $log->template_log_id;
			for ($j = 0; $j < count($request->input('exercise_name')[$i]); $j++)
			{
				// save the exercise
				$exercise = new \App\Template_log_exercise;
				$exercise->template_log_id = $log_id;
				// find exercise
				$exercise_data = \App\Template_exercises::firstOrCreate(['texercise_name' => $request->input('exercise_name')[$i][$j]]);
				$exercise->texercise_id = $exercise_data->texercise_id;
				$exercise->texercise_name = $request->input('exercise_name')[$i][$j];
				$exercise->logtempex_order = $j;
				$exercise->save();
				$exercise_id = $exercise->logtempex_id;
				for ($k = 0; $k < count($request->input('item_type')[$i][$j]); $k++)
				{
					// save the item
					$item = new \App\Template_log_items;
					$item->template_log_id = $log_id;
					$item->logtempex_id = $exercise_id;
					$item->texercise_id = $exercise_data->texercise_id;
					switch ($request->input('item_type')[$i][$j][$k])
					{
						case 'W':
							$item->is_weight = true;
							$item->logtempitem_weight = $request->input('item_value')[$i][$j][$k];
							break;
						case 'P':
							$item->is_percent_1rm = true;
							$item->percent_1rm = $request->input('item_value')[$i][$j][$k];
							if ($log->has_fixed_values)
							{
								$log->has_fixed_values = false;
								$log->save();
							}
							break;
						case 'RM':
							$item->is_current_rm = true;
							$item->current_rm = $request->input('item_value')[$i][$j][$k];
							if ($log->has_fixed_values)
							{
								$log->has_fixed_values = false;
								$log->save();
							}
						break;
						case 'T':
							$item->is_time = true;
							$item->logtempitem_time = $request->input('item_value')[$i][$j][$k];
						break;
						case 'D':
							$item->is_distance = true;
							$item->logtempitem_distance = $request->input('item_value')[$i][$j][$k];
						break;
					}
					if (floatval($request->input('item_plus')[$i][$j][$k]) > 0)
					{
						$item->has_plus_weight = true;
						$item->logtempitem_plus_weight = $request->input('item_plus')[$i][$j][$k];
					}
					if (floatval($request->input('item_rpe')[$i][$j][$k]) > 0)
					{
						$item->is_pre = true;
						$item->logtempitem_pre = $request->input('item_rpe')[$i][$j][$k];
					}
					$item->logtempitem_reps = $request->input('item_reps')[$i][$j][$k];
					$item->logtempitem_sets = $request->input('item_sets')[$i][$j][$k];
					$item->logtempitem_comment = $request->input('item_comment')[$i][$j][$k];
					$item->logtempitem_order = $k;
					$item->logtempex_order = $j;
					$item->save();
				}
			}
		}
	}
}
