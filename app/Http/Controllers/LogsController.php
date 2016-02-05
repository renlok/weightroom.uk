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
        $user = User::where('user_name', $user_name)->firstOrFail();
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
                        $log->average_intensity = Format::correct_weight($log->average_intensity, 'kg', Auth::user()->user_unit);
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
        $user = User::find(Auth::user()->user_id)->firstOrFail();
        $log = Log::where('log_date', $date)
                    ->select('log_text', 'log_weight', 'log_update_text')
                    ->where('user_id', $user->user_id)
                    ->firstOrFail();
        if ($log->log_update_text == 1)
		{
			$log->log_text = Parser::rebuild_log_text ($user->user_id, $date);
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
        $parser = new Parser;
        $weight = $parser->get_input_weight($request->input('weight'), $date);
        $parser->parse_text ($request->input('log'));
		$parser->store_log_data ($date, $weight, false);
        return redirect()
                ->route('viewLog', ['date' => $date])
                ->with([
                    'flash_message' => 'Workout saved.'
                ]);
    }

    public function getNew($date)
    {
        $user = User::find(Auth::user()->user_id)->firstOrFail();
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
        $parser = new Parser;
        $weight = $parser->get_input_weight($request->input('weight'), $date);
        $parser->parse_text ($request->input('log'));
		$parser->store_log_data ($date, $weight, true);
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
                        ->where('log_items.user_id', $user->user_id)
                        ->where('log_items.logitem_weight', $request->old('weightoperator'), Format::correct_weight($request->old('weight'), $user->user_unit, 'kg'))
                        ->where('exercises.exercise_name', $request->old('exercise'));
            if ($request->old('reps') != 'any' && $request->old('reps') != '')
        	{
        		$query = $query->where('log_items.logitem_reps', $request->old('reps'));
        	}
            $query = $query->orderBy('log_items.log_date', 'desc')
                            ->groupBy('logex_id');
            if ($request->old('show') > 0)
        	{
        		$query = $query->take($request->old('show'));
        	}
            $query = $query->lists('logex_id');
            $log_exercises = Log_exercise::whereIn('logex_id', $query)->get();
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
            $from_date = Log::where('user_id', Auth::user()->user_id)->orderBy('log_date', 'asc')->value('log_date')->toDateString();
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
}
