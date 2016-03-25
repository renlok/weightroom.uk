<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Exercise;
use App\Exercise_record;
use App\Logs;
use App\Log_exercise;
use App\Log_item;
use App\Extend\PRs;
use App\Extend\Format;
use Carbon\Carbon;

class ExercisesController extends Controller
{
	public function getList()
	{
		$exercises = Exercise::listexercises(true)->paginate(50);
		return view('exercise.list', compact('exercises'));
	}

	public function getEdit($exercise_name)
	{
		$exercise = Exercise::select('is_time', 'is_endurance', 'is_distance', 'exercise_id')->where('exercise_name', $exercise_name)->where('user_id', Auth::user()->user_id)->firstOrFail();
		$current_type = 'weight';
		if ($exercise->is_distance)
		{
			$current_type = 'distance';
		}
		elseif ($exercise->is_endurance)
		{
			$current_type = 'enduracne';
		}
		elseif ($exercise->is_time)
		{
			$current_type = 'time';
		}
		$goals = Exercise_goal::where('exercise_id', $exercise->exercise_id)->get();
		return view('exercise.edit', compact('exercise_name', 'current_type', 'goals'));
	}

	public function postEditName($exercise_name, Request $request)
	{
		$new_name = $request->input('exercisenew');
		$exercise_old = Exercise::where('exercise_name', $exercise_name)->where('user_id', Auth::user()->user_id)->firstOrFail();
		$exercise_new = Exercise::where('exercise_name', $new_name)->where('user_id', Auth::user()->user_id)->first();
		// new name already exists
		if($exercise_new != null)
		{
			$final_id = $exercise_new->$exercise_id;
			// remove the old exercise and merge it with an exsisting one
			// update the exercise id
			DB::table('log_exercises')
				->where('exercise_id', $exercise_old->$exercise_id)
				->update(['exercise_id' => $exercise_new->$exercise_id]);
			// update PRs
			PRs::rebuildExercisePRs($exercise_new->$exercise_id);
			// delete the old PRs
			DB::table('exercise_records')
				->where('exercise_id', $exercise_old->$exercise_id)
				->delete();
			// delete the old exercise
			$exercise_old->delete();
		}
		else
		{
			$final_id = $exercise_old->exercise_id;
			// rename the exercise
			$exercise_old->exercise_name = $new_name;
			$exercise_old->save();
		}
		// update the log texts
		DB::table('logs')
			->join('log_exercises', 'logs.log_id', '=', 'log_exercises.log_id')
			->where('logs.user_id', Auth::user()->user_id)
			->where('log_exercises.exercise_id', $final_id)
			->update(['logs.log_update_text' => 1]);
		return redirect()
			->route('viewExercise', ['exercise_name' => $new_name])
			->with(['flash_message' => "$exercise_name shall be now known as $new_name"]);
	}

	public function postEdit($exercise_name, Request $request)
	{
		$new_type = $request->input('exerciseType');
		$exercise = Exercise::select('exercise_id')->where('exercise_name', $exercise_name)->where('user_id', Auth::user()->user_id)->firstOrFail();
		$update = ['is_time' => false, 'is_endurance' => false, 'is_distance' => false];
		if ($new_type == 'time')
		{
			$update['is_time'] = true;
		}
		elseif ($new_type == 'enduracne')
		{
			$update['is_time'] = true;
			$update['is_endurance'] = true;
		}
		elseif ($new_type == 'distance')
		{
			$update['is_distance'] = true;
		}
		// update the log texts
		DB::table('exercises')
			->where('exercise_id', $exercise->exercise_id)
			->update($update);
		return redirect()
			->route('viewExercise', ['exercise_name' => $exercise_name])
			->with(['flash_message' => "$exercise_name has been updated"]);
	}

	public function history($exercise_name, $from_date = '', $to_date = '')
	{
		$user = Auth::user();
		$exercise = Exercise::where('exercise_name', $exercise_name)
					->where('user_id', $user->user_id)->firstOrFail();
		$query = $exercise->log_exercises();
		if (!empty($from_date))
		{
			$query = $query->where('log_date', '>=', $from_date);
		}
		if (!empty($to_date))
		{
			$query = $query->where('log_date', '<=', $to_date);
		}
		$scales = [
			'logex_reps' => 1,
			'logex_sets' => 1,
			'logex_1rm' => 1,
		];
		// set scales
		if ($exercise->is_time)
		{
			$max_volume = Format::correct_time($query->max('logex_time'), 's', 'h');
			$table_name = 'logex_time';
			$scales['logex_time'] = 1;
			$graph_names = ['logex_time' => 'Total Time'];
		}
		elseif ($exercise->is_distance)
		{
			$max_volume = Format::correct_distance($query->max('logex_distance'), 'm', 'km');
			$table_name = 'logex_distance';
			$scales['logex_distance'] = 1;
			$graph_names = ['logex_distance' => 'Total Distance'];
		}
		else
		{
			$max_volume = Format::correct_weight($query->max('logex_volume'));
			$table_name = 'logex_volume';
			$max_reps = $query->max('logex_reps');
			$max_sets = $query->max('logex_sets');
			$max_rm = Exercise_record::exercisemaxpr($user->user_id, $exercise->exercise_id, $exercise->is_time, $exercise->is_endurance, $exercise->is_distance);
			$scales = [
				'logex_volume' => 1,
				'logex_reps' => floor($max_volume / $max_reps),
				'logex_sets' => floor($max_volume / $max_sets),
				'logex_1rm' => floor($max_volume / $max_rm),
			];
			$graph_names = [
				'logex_volume' => 'Volume',
				'logex_reps' => 'Total reps',
				'logex_sets' => 'Total sets',
				'logex_1rm' => '1RM',
			];
		}
		// get log_exercises
		$log_exercises = $query->orderBy('log_date', 'desc')->get();
		return view('exercise.history', compact('exercise_name', 'graph_names', 'log_exercises', 'scales', 'user'));
	}

	public function volume($exercise_name)
	{
		// TODO
		return view('exercise.volume');
	}

	public function getViewExercise($exercise_name, $type = 'prs', $range = 0, $force_pr_type = null)
	{
		$exercise = Exercise::getexercise($exercise_name, Auth::user()->user_id)->firstOrFail();
		$query = Exercise_record::getexerciseprs(Auth::user()->user_id, Carbon::now()->toDateString(), $exercise_name, $exercise, true)->get();
		$current_prs = $query->groupBy('pr_reps')->toArray();
		$filtered_prs = Exercise_record::filterPrs($query);
		if ($type == 'prs')
		{
			$prs = Exercise_record::getexerciseprsall(Auth::user()->user_id, $range, $exercise_name, $exercise, Auth::user()->user_showreps)->get()->groupBy('pr_reps');
			if (!($exercise->is_time || $exercise->is_distance))
			{
				$prs['Approx. 1'] = Exercise_record::getest1rmall(Auth::user()->user_id, $range, $exercise_name, Auth::user()->user_showreps)->get();
				// be in format [1 => ['log_weight' => ??, 'log_date' => ??]]
				$approx1rm = $prs['Approx. 1']->last()->pr_value;
			}
			else
			{
				$approx1rm = 0;
			}
		}
		else
		{
			$prs = Log_item::getexercisemaxes(Auth::user()->user_id, $range, $exercise_name, $exercise, Auth::user()->user_showreps, $type)->get()->groupBy('logitem_reps');
			$prs['Approx. 1'] = Log_item::getestimatedmaxes(Auth::user()->user_id, $range, $exercise_name, $exercise, $type)->get();
			$approx1rm = Exercise_record::getlastest1rm(Auth::user()->user_id, $exercise_name)->value('pr_1rm');
		}
		$graph_label = 'Weight';
		$format_func = 'correct_weight';
		$show_prilepin = true;
		if ($exercise->is_time)
		{
			$graph_label = 'Time';
			$format_func = 'format_time';
			$show_prilepin = false;
		}
		elseif ($exercise->is_distance)
		{
			$graph_label = 'Distance';
			$format_func = 'format_distance';
			$show_prilepin = false;
		}

		return view('exercise.view', compact('exercise_name', 'current_prs', 'filtered_prs', 'prs', 'range', 'type', 'graph_label', 'format_func', 'show_prilepin', 'approx1rm'));
	}

	public function getViewExercisePRHistory($exercise_name)
	{
		$exercise = Exercise::getexercise($exercise_name, Auth::user()->user_id)->firstOrFail();
		$prs = Exercise_record::getexerciseprsall(Auth::user()->user_id, 0, $exercise_name, $exercise)->get()->groupBy(function ($item, $key) {
			return $item['log_date']->toDateString();
		})->toArray();
		$prs = array_map(function($collection) {
			$temp = [];
			foreach ($collection as $item)
			{
				$temp[$item['pr_reps']] = $item['pr_value'];
			}
			return $temp;
		}, $prs);
		krsort ($prs);
		$format_func = 'correct_weight';
		if ($exercise->is_time)
		{
			$format_func = 'format_time';
		}
		elseif ($exercise->is_distance)
		{
			$format_func = 'format_distance';
		}
		return view('exercise.prhistory', compact('exercise_name', 'exercise', 'prs', 'format_func'));
	}

	public function getCompareForm()
	{
		$exercises = Exercise::listexercises(true)->get();
		$exercise_names = [];
		return view('exercise.compareform', compact('exercises', 'exercise_names'));
	}

	public function getCompare($reps = '', $exercise1 = '', $exercise2 = '', $exercise3 = '', $exercise4 = '', $exercise5 = '')
	{
		$exercises = Exercise::listexercises(true)->get();
		$exercise_names = array_map('strtolower', array_filter([$exercise1, $exercise2, $exercise3, $exercise4, $exercise5]));
		$pr_value = ($reps > 0) ? 'pr_value' : 'pr_1rm';
		$records = DB::table('exercise_records')
			->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
			->select(DB::raw("MAX($pr_value) as pr_value"), 'pr_reps', 'log_date', 'exercise_name')
			->where('exercise_records.user_id', Auth::user()->user_id)
			->whereIn('exercise_name', $exercise_names);
		if ($reps > 0)
		{
			$records = $records->where('pr_reps', $reps);
		}
		else
		{
			$records = $records->where('is_est1rm', 1);
		}
		$records = $records->groupBy(DB::raw('log_date, exercise_name'))->orderBy('log_date', 'asc')->get();
		return view('exercise.compare', compact('exercises', 'records', 'exercise1', 'exercise2', 'exercise3', 'exercise4', 'exercise5', 'exercise_names'));
	}

	public function postCompare(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'reps' => 'required|between:0,10',
			'exercises.0' => 'required',
			'exercises.*' => 'exists:exercises,exercise_name,user_id,'.Auth::user()->user_id
		]);
		if ($validator->fails()) {
			return redirect('exercise/compare')
						->withErrors($validator)
						->withInput();
		}
		$exercises = $request->input('exercises');
		$route_data = ['reps' => $request->input('reps'), 'exercise1' => $exercises[0]];
		if (isset($exercises[1]))
		{
			$route_data['exercise2'] = $exercises[1];
		}
		if (isset($exercises[2]))
		{
			$route_data['exercise3'] = $exercises[2];
		}
		if (isset($exercises[3]))
		{
			$route_data['exercise4'] = $exercises[3];
		}
		if (isset($exercises[4]))
		{
			$route_data['exercise5'] = $exercises[4];
		}
		return redirect()
			->route('compareExercises', $route_data);
	}
}
