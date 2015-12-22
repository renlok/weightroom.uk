<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Log;
use Auth;

class LogsController extends Controller
{
    public function index()
    {
        return $this->view();
    }

    public function view($date, $user_name = Auth::user()->user_id)
    {
        $user = User::where('user_name', $user_name)->firstOrFail();
        $log = $user->log->getlog($date, $user);
        $is_log = $log->count();
        $is_following = $user->invite_code->where('follow_user_id', $user->user_id)->count();
        return view('log.view', compact('date', 'user', 'log', 'is_following', 'is_log'));
    }

    public function getEdit()
    {
        return view('log.edit');
    }

    public function postEdit(LogRequest $requests)
    {
        // TODO finish this
        $log = Log()::find($log_id);
        Auth::user()->logs()->save($log);
        return redirect('viewLog', ['date' => '']);
    }

    public function getNew()
    {
        return view('log.new');
    }

    public function postNew(LogRequest $requests)
    {
        $log = new Log($requests->all());
        Auth::user()->logs()->save($log);
        return redirect('viewLog', ['date' => '']);
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
