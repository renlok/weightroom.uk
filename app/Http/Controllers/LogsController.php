<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Validator;
use App\Comment;
use App\Exercise;
use App\Log;
use App\Log_exercise;
use App\User;
use App\Extend\PRs;
use App\Extend\Parser;
use App\Extend\Log_control;
use App\Extend\Format;
use App\Http\Requests;
use App\Http\Requests\LogRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LogsController extends Controller
{
	public function viewUser($user_name)
	{
		$user = User::where('user_name', $user_name)->firstOrFail();
		$last_log = $user->logs()->orderBy('log_date', 'desc')->first();
		if ($last_log != null)
		{
			$date = $last_log->log_date->toDateString();
		}
		else
		{
			$date = Carbon::now()->toDateString();
		}
		return $this->view($date, $user_name);
	}

	public function view($date, $user_name = '')
	{
		if ($user_name == '')
		{
			$user_name = Auth::user()->user_name;
		}
		$user = User::with('logs.log_exercises.log_items', 'logs.log_exercises.exercise')
				->where('user_name', $user_name)->firstOrFail();
		$log = $user->logs()->where('log_date', $date)->first();
		if ($log != null)
		{
			if (Auth::user()->user_showintensity != 'h')
			{
				$log->average_intensity = 0;
				$count = 0;
				foreach ($log->log_exercises()->get() as $log_exercises)
				{
					$log->average_intensity += $log_exercises->average_intensity_raw;
					$count++;
				}
				if ($count > 0)
				{
					if (Auth::user()->user_showintensity == 'p')
					{
						$ai_suffix = '%';
					}
					else
					{
						// correct format
						$log->average_intensity = Format::correct_weight($log->average_intensity);
						$ai_suffix = ' ' . Auth::user()->user_unit;
					}
					$log->average_intensity = round($log->average_intensity/$count) . $ai_suffix;
				}
			}
			else
			{
				$log->average_intensity = '';
			}
			$comments = Comment::where('commentable_id', $log->log_id)->where('commentable_type', 'App\Log')->where('parent_id', 0)->orderBy('comment_date', 'asc')->withTrashed()->get();
		}
		else
		{
			$comments = null;
		}
		$is_following = (DB::table('user_follows')->where('user_id', Auth::user()->user_id)->where('follow_user_id', $user->user_id)->first() == null) ? false : true;
		if (!isset($commenting))
		{
			$commenting = false;
		}
		$carbon_date = Carbon::createFromFormat('Y-m-d', $date);
		$calender = Log_control::preload_calender_data($date, $user->user_id);
		return view('log.view', compact('date', 'carbon_date', 'user', 'log', 'comments', 'is_following', 'commenting', 'calender'));
	}

	public function getEdit($date)
	{
		$user = Auth::user();
		$log = Log::select('log_text', 'log_weight', 'log_update_text')
					->where('log_date', $date)
					->where('user_id', $user->user_id)
					->firstOrFail();
		if ($log->log_update_text == 1)
		{
			$log->log_text = Log_control::rebuild_log_text ($user->user_id, $date);
		}
		$type = 'edit';
		$exercise_list = Exercise::listexercises(true)->get();
		$exercises = '';
		foreach ($exercise_list as $exercise)
		{
			if ($exercises != '')
			{
				$exercises .= ',';
			}
			$exercises .= "[\"{$exercise['exercise_name']}\", {$exercise['COUNT']}]";
		}
		$calender = Log_control::preload_calender_data($date, $user->user_id);
		return view('log.edit', compact('date', 'log', 'user', 'type', 'exercises', 'calender'));
	}

	public function postEdit($date, LogRequest $request)
	{
		$parser = new Parser($request->input('log'), $date, $request->input('weight'));
		$parser->parseText ();
		$parser->formatLogData (false);
		$parser->saveLogData ();
		return redirect()
				->route('viewLog', ['date' => $date])
				->with([
					'flash_message' => 'Workout saved.'
				]);
	}

	public function getNew($date)
	{
		$user = Auth::user();
		$log = [
			'log_text' => '',
			'log_weight' => Log::getlastbodyweight(Auth::user()->user_id, $date)->value('log_weight'),
		];
		$type = 'new';
		$exercise_list = Exercise::listexercises(true)->get();
		$exercises = '';
		foreach ($exercise_list as $exercise)
		{
			if ($exercises != '')
			{
				$exercises .= ',';
			}
			$exercises .= "[\"{$exercise['exercise_name']}\", {$exercise['COUNT']}]";
		}
		$calender = Log_control::preload_calender_data($date, $user->user_id);
		return view('log.edit', compact('date', 'log', 'user', 'type', 'exercises', 'calender'));
	}

	public function postNew($date, LogRequest $request)
	{
		$parser = new Parser($request->input('log'), $date, $request->input('weight'));
		$parser->parseText ();
		$parser->formatLogData (true);
		$parser->saveLogData ();
		return redirect()
				->route('viewLog', ['date' => $date])
				->with([
					'flash_message' => 'Workout saved.'
				]);
	}

	public function delete($date)
	{
		$exercise_ids = DB::table('log_exercises')
							->where('log_date', $date)
							->where('user_id', Auth::user()->user_id)
							->distinct()
							->lists('exercise_id');
		DB::table('log_items')->where('log_date', $date)->where('user_id', Auth::user()->user_id)->delete();
		DB::table('log_exercises')->where('log_date', $date)->where('user_id', Auth::user()->user_id)->delete();
		DB::table('exercise_records')->where('log_date', $date)->where('user_id', Auth::user()->user_id)->delete();
		DB::table('logs')->where('log_date', $date)->where('user_id', Auth::user()->user_id)->delete();
		foreach ($exercise_ids as $exercise_id)
		{
			PRs::rebuildExercisePRs($exercise_id);
		}
		return redirect()
				->route('viewLog', ['date' => $date])
				->with([
					'flash_message' => 'Workout deleted.',
					'flash_message_type' => 'danger'
				]);
	}

	public function getAjaxcal($date, $user_name)
	{
		$user = User::where('user_name', $user_name)->firstOrFail();
		$month = Carbon::createFromFormat('Y-m', $date);
		$log_dates = Log::where('user_id', $user->user_id)
						->whereBetween('log_date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()])
						->lists('log_date');
		return response()->json(['dates' => $log_dates, 'cals' => $date]);
	}

	public function postSearch(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'show' => 'required|integer',
			'exercise' => 'exists:exercises,exercise_name,user_id,'.Auth::user()->user_id,
			'weightoperator' => 'required|in:=,>=,<=,<,>',
			'valuetype' => 'required|in:weight,distance,time',
			'weight' => 'required|numeric',
			'orderby' => 'required|in:asc,desc',
		]);

		if ($validator->fails())
		{
			return redirect()
					->route('searchLog')
					->withErrors($validator)
					->withInput();
		}

		return redirect()
				->route('searchLog')
				->withInput();
	}

	public function getSearch(Request $request)
	{
		$user = Auth::user();
		if ($request->old('exercise') != null)
		{
			$query = DB::table('log_items')
						->join('exercises', 'exercises.exercise_id', '=', 'log_items.exercise_id')
						->where('log_items.user_id', $user->user_id);
			if ($request->old('valuetype') == 'distance')
			{
				$query = $query->where('log_items.logitem_distance', $request->old('weightoperator'), Format::correct_distance($request->old('weight'), 'km', 'm'));
			}
			elseif ($request->old('valuetype') == 'time')
			{
				$query = $query->where('log_items.logitem_time', $request->old('weightoperator'), Format::correct_time($request->old('weight'), 'h', 's'));
			}
			else
			{
				$query = $query->where('log_items.logitem_weight', $request->old('weightoperator'), Format::correct_weight($request->old('weight'), $user->user_unit, 'kg'));
			}
			$query = $query->where('exercises.exercise_name', $request->old('exercise'));
			if ($request->old('reps') != 'any' && $request->old('reps') != '')
			{
				$query = $query->where('log_items.logitem_reps', $request->old('reps'));
			}
			$query = $query->groupBy('logex_id');
			if ($request->old('show') > 0)
			{
				$query = $query->take($request->old('show'));
			}
			$query = $query->lists('logex_id');
			$log_exercises = Log_exercise::whereIn('logex_id', $query)->orderBy('log_date', $request->old('orderby'))->get();
		}
		else
		{
			$log_exercises = [];
		}
		$exercises = Exercise::listexercises(false)->get();
		return view('log.search', compact('exercises', 'log_exercises', 'user'));
	}

	public function getVolume($from_date = 0, $to_date = 0, $n = 0)
	{
		$query = DB::table('logs')
					->where('user_id', Auth::user()->user_id);
		if ($from_date != 0)
		{
			$query = $query->where('log_date', '>=', $from_date);
		}
		else
		{
			$from_date_query = Log::where('user_id', Auth::user()->user_id)->orderBy('log_date', 'asc')->value('log_date');
			if ($from_date_query != null)
			{
				$from_date = $from_date_query->toDateString();
			}
			else
			{
				$from_date = Carbon::now()->toDateString();
			}
		}
		if ($to_date != 0)
		{
			$query = $query->where('log_date', '<=', $to_date);
		}
		else
		{
			$to_date = Carbon::now()->toDateString();
		}
		$max_volume = Format::correct_weight($query->max('log_total_volume'));
		$scales = [
			'log_total_volume' => 1,
			'log_total_reps' => floor($max_volume / $query->max('log_total_reps')),
			'log_total_sets' => floor($max_volume / $query->max('log_total_sets')),
		];
		$graph_data = $query->orderBy('log_date', 'asc')->get();
		$graph_names = [
			'log_total_volume' => 'Volume',
			'log_total_reps' => 'Total reps',
			'log_total_sets' => 'Total sets',
		];
		if ($n > 0)
		{
			$graph_data = Log_control::calculate_moving_average($graph_data, array_keys($graph_names), $n);
		}
		return view('log.volume', compact('from_date', 'to_date', 'n', 'scales', 'graph_names', 'graph_data'));
	}

	public function postVolume(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'to_date' => 'required|date_format:Y-m-d',
			'from_date' => 'required|date_format:Y-m-d',
			'n' => 'required|in:0,3,5,7',
		]);

		if ($validator->fails())
		{
			return redirect()
					->route('totalVolume')
					->withErrors($validator)
					->withInput();
		}

		return redirect()
				->route('totalVolume', [
					'to_date' => $request->input('to_date'),
					'from_date' => $request->input('from_date'),
					'n' => $request->input('n')
				]);
	}

	public function getReports(Request $request)
	{
		if ($request->old('view_type') == '' || $request->old('view_type') == 'volume')
		{
			$graph_values = Log::select(DB::raw('SUBDATE(log_date, WEEKDAY(log_date)) as log_date, SUM(log_total_volume) as graph_value'))
								->where('user_id', Auth::user()->user_id)
								->groupBy(DB::raw('YEAR(log_date), WEEK(log_date)'))
								->get()->toArray();
			$key_label = 'volume';
		}
		$show_moving_average = false;
		if ($request->old('n') > 0)
		{
			$show_moving_average = true;
			$moving_avg = Log_control::calculate_moving_average($graph_values, ['graph_value'], intval($request->old('n')));
		}
		$exercises = Exercise::listexercises(true)->get();
		$graph_label = 'something';
		return view('log.reports', compact('exercises', 'key_label', 'graph_values', 'moving_avg', 'graph_label', 'show_moving_average'));
	}

	public function ajaxGetReport(Request $request)
	{
		$view_type = $request->input('view_type', 'volume');
		$exercise_view = $request->input('exercise_view', 'everything');
		// load graph type
		$main_table = 'logs';
		if ($view_type == 'volume')
		{
			$graph_value = 'logs.log_total_volume';
			if ($request->old('ignore_warmups', 0))
			{
				$graph_value = 'logs.log_total_volume - logs.log_warmup_volume';
			}
			$graph_values = Log::select(DB::raw('SUBDATE(' . $main_table . '.log_date, WEEKDAY(' . $main_table . '.log_date)) as value_date, SUM(' . $graph_value . ') as graph_value'));
		}
		else if ($view_type == 'intensity')
		{
			$main_table = 'log_exercises';
			$graph_value = 'log_exercises.logex_inol';
			if ($request->old('ignore_warmups', 0))
			{
				$graph_value = 'log_exercises.logex_inol - log_exercises.logex_inol_warmup';
			}
			$graph_values = Log_exercise::select(DB::raw('SUBDATE(' . $main_table . '.log_date, WEEKDAY(' . $main_table . '.log_date)) as value_date, SUM(' . $graph_value . ') as graph_value'));
		}
		else if ($view_type == 'setsweek')
		{
			$graph_values = Log::select(DB::raw('SUBDATE(' . $main_table . '.log_date, WEEKDAY(' . $main_table . '.log_date)) as value_date, SUM(logs.log_total_sets) as graph_value'));
		}
		else if ($view_type == 'workoutsweek')
		{
			$graph_values = Log::select(DB::raw('SUBDATE(' . $main_table . '.log_date, WEEKDAY(' . $main_table . '.log_date)) as value_date, COUNT(logs.log_id) as graph_value'));
		}
		// limit to exercises
		if ($exercise_view != 'everything')
		{
			if ($main_table != 'log_exercises')
			{
				$graph_values = $graph_values->join('log_exercises', "$main_table.log_id", '=', 'log_exercises.log_id');
			}
			$graph_values = $graph_values->join('exercises', 'log_exercises.exercise_id', '=', 'exercises.exercise_id');
			if ($exercise_view == 'powerlifting')
			{
				$graph_values = $graph_values->whereIn('exercises.exercise_id', [Auth::user()->user_squatid, Auth::user()->user_deadliftid, Auth::user()->user_benchid]);;
			}
			elseif ($exercise_view == 'weightlifting')
			{
				$graph_values = $graph_values->whereIn('exercises.exercise_id', [Auth::user()->user_snatchid, Auth::user()->user_cleanjerkid]);
			}
			elseif(intval($exercise_view) == $exercise_view)
			{
				$graph_values = $graph_values->where('exercises.exercise_id', $exercise_view);
			}
		}
		$graph_values = $graph_values->where("$main_table.user_id", Auth::user()->user_id)
						->groupBy(DB::raw('YEAR(value_date), WEEK(value_date)'))
						->get();
		$return_values = [];
		$min_date = 0;
		$max_date = 0;
		foreach ($graph_values as $value)
		{
			if ($min_date == 0) $min_date = $value->value_date;
			$max_date = $value->value_date;
			$return_values[$value->value_date] = (float)$value->graph_value;
		}
		$blanks = DB::table('weeks')->whereBetween('week', [$min_date, $max_date])->pluck('empty', 'week');
		return response()->json(array_merge($blanks, $return_values));
	}
}
