<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use App\Comment;
use App\Exercise;
use App\Log;
use App\Log_exercise;
use App\User;
use App\Extend\PRs;
use App\Extend\Parser;
use App\Http\Requests;
use App\Http\Requests\LogRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LogsController extends Controller
{
    public function viewUser($user_name)
    {
        return $this->view(Carbon::now()->toDateString, $user_name);
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
            //TODO fix this
            //$log->average_intensity = $log->log_exercises()->sum('average_intensity');
            $log->average_intensity = '';
        }
        if ($user->invite_code == null)
        {
            $is_following = 0;
        }
        else
        {
            $is_following = $user->invite_code->where('follow_user_id', $user->user_id)->count();
        }
        if (!isset($commenting))
        {
            $commenting = false;
        }
        $carbon_date = Carbon::createFromFormat('Y-m-d', $date);
        return view('log.view', compact('date', 'carbon_date', 'user', 'log', 'is_following', 'commenting'));
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
        return view('log.edit', compact('date', 'log', 'user', 'type', 'exercises'));
    }

    public function postEdit($date, LogRequest $request)
    {
        $parser = new Parser;
        $weight = $parser->get_input_weight($request->input('weight'), $date);
        $parser->parse_text ($request->input('log'));
		$new_prs = $parser->store_log_data ($date, $weight, false);
        return redirect()
                ->route('viewLog', ['date' => $date])
                ->with('new_prs', $new_prs);
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
        return view('log.edit', compact('date', 'log', 'user', 'type', 'exercises'));
    }

    public function postNew($date, LogRequest $request)
    {
        $parser = new Parser;
        $weight = $parser->get_input_weight($request->input('weight'), $date);
        $parser->parse_text ($request->input('log'));
		$new_prs = $parser->store_log_data ($date, $weight, true);
        return redirect()
                ->route('viewLog', ['date' => $date])
                ->with('new_prs', $new_prs);
    }

    public function delete($date)
    {
        DB::table('logs')->where('log_date', $date)->where('user_id', Auth::user()->user_id)->delete();
        return redirect()
                ->route('viewLog', ['date' => $date]);
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
                        ->where('log_items.logitem_weight', $request->old('weightoperator'), $request->old('weight'))
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

    public function getVolume($from_date = 0, $to_date = 0)
    {
        $query = DB::table('logs')
                    ->where('user_id', Auth::user()->user_id);
        if ($from_date != 0)
        {
            $query = $query->where('log_date', '>=', $from_date);
        }
        if ($to_date != 0)
        {
            $query = $query->where('log_date', '<=', $to_date);
        }
        $max_volume = $query->max('log_total_volume');
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
        return view('log.volume', compact('from_date', 'to_date', 'scales', 'graph_names', 'graph_data'));
    }

    public function postVolume(Request $request)
    {
        return redirect()
                ->route('totalVolume', ['to_date' => $request->input('to_date'), 'from_date' => $request->input('from_date')]);
    }
}
