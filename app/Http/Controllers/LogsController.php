<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\LogRequest;
use App\Http\Controllers\Controller;
use App\User;
use App\Log;
use App\Exercise;
use Auth;
use App\Extend\PRs;
use App\Extend\Parser;
use Carbon\Carbon;

class LogsController extends Controller
{
    public function index($user_name = '')
    {
        return $this->view();
    }

    public function view($date, $user_name = '')
    {
        if ($user_name == '')
        {
            $user_name = Auth::user()->user_name;
        }
        $user = User::where('user_name', $user_name)->firstOrFail();
        if ($user->logs != null)
        {
            $log = $user->logs()->where('log_date', $date)->first();
        }
        else
        {
            $log = null;
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
                    ->firstOrFail()
                    ->toArray();
        if ($log['log_update_text'] == 1)
		{
			$log['log_text'] = Parser::rebuild_log_text ($user->user_id, $date);
		}
        $type = 'edit';
        $exercise_list = Exercise::listexercises(true)->get();
        $exercises = [];
        foreach ($exercise_list as $exercise)
        {
            $exercises[$exercise['exercise_name']] = $exercise['COUNT'];
        }
        $exercises = $exercises->toJson();
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
        $exercises = [];
        foreach ($exercise_list as $exercise)
        {
            $exercises[$exercise['exercise_name']] = $exercise['COUNT'];
        }
        $exercises = $exercises->toJson();
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
        return redirect('viewLog', ['date' => $date]);
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

    public function search()
    {
        return view('log.search');
    }

    public function volume()
    {
        return view('log.volume');
    }
}
