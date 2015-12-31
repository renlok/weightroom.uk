<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Log;
use Auth;
use App\Extends\PRs;
use App\Extends\Parser;

class LogsController extends Controller
{
    public function index()
    {
        return $this->view();
    }

    public function view($date, $user_name = Auth::user()->user_name)
    {
        $user = User::where('user_name', $user_name)->firstOrFail();
        $log = $user->log->getlog($date, $user);
        $is_log = $log->count();
        $is_following = $user->invite_code->where('follow_user_id', $user->user_id)->count();
        return view('log.view', compact('date', 'user', 'log', 'is_following', 'is_log'));
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
        return view('log.edit', compact('date', 'log', 'user'));
    }

    public function postEdit($date, LogRequest $requests)
    {
        $parser = new Parser;
        $weight = $parser->get_input_weight($request->input('weight'), $date);
        $parser->parse_text ($request->input('log'));
		$new_prs = $parser->store_log_data ($date, $weight, false);
        return redirect('viewLog', ['date' => $date])
                ->with('new_prs', $new_prs);
    }

    public function getNew($date)
    {
        $user = User::find(Auth::user()->user_id)->firstOrFail();
        $log = collect([
            'log_text' => '',
            'log_weight' Log::getlastbodyweight->get(),
        ]);
        return view('log.edit', compact('date', 'log', 'user'));
    }

    public function postNew($date, LogRequest $requests)
    {

        $parser = new Parser;
        $weight = $parser->get_input_weight($request->input('weight'), $date);
        $parser->parse_text ($request->input('log'));
		$new_prs = $parser->store_log_data ($date, $weight, true);
        return redirect('viewLog', ['date' => $date])
                ->with('new_prs', $new_prs);
    }

    public function delete($date)
    {
        DB::table('logs')->where('log_date', $date)->where('user_id', Auth::user()->user_id)->delete();
        return redirect('viewLog', ['date' => $date]);
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
