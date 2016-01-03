<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\Log;
use App\Log_item;

class ToolsController extends Controller
{
    public function index()
    {
        return view('tools.index');
    }

    public function bodyweight($range = 0)
    {
        $graphs = [];
        $graphs['Bodyweight'] = Log::getbodyweight(Auth::user()->user_id)->get();
        return view('tools.bodyweight', compact('range', 'graphs'));
    }

    public function wilks($range = 0)
    {
        $graph_data = Log_item::select('logitem_date', 'exercise_name', 'logitem_abs_weight as log_weight')
                                ->join('exercises', 'log_items.exercise_id', '=', 'exercises.exercise_id')
                                ->where('log_items.user_id', Auth::user()->user_id)
                                ->where('is_pr', 1)
                                ->whereIn('log_items.exercise_id', [Auth::user()->user_squatid, Auth::user()->user_deadliftid, Auth::user()->user_benchid])
                                ->orderBy('logitem_date', 'asc');
        $graphs = $graph_data->get()->groupBy('exercise_name')->toArray();
        $graphs['Bodyweight'] = Log::getbodyweight(Auth::user()->user_id)->get();
        $graphs['Wilks'] = []; //TODO ->map()
        $graphs->map(function ($item, $key) {
            return $item * 2;
        });
        return view('tools.wilks', compact('range', 'graphs'));
    }

    public function sinclair($range = 0)
    {
        $graph_data = Log_item::select('logitem_date', 'exercise_name', 'logitem_abs_weight as log_weight')
                                ->join('exercises', 'log_items.exercise_id', '=', 'exercises.exercise_id')
                                ->where('log_items.user_id', Auth::user()->user_id)
                                ->where('is_pr', 1)
                                ->whereIn('log_items.exercise_id', [Auth::user()->user_snatchid, Auth::user()->user_cleanjerkid])
                                ->orderBy('logitem_date', 'asc');
        $graphs = $graph_data->get()->groupBy('exercise_name')->toArray();
        $graphs['Bodyweight'] = Log::getbodyweight(Auth::user()->user_id)->get();
        $graphs['Sinclair'] = []; //TODO
        return view('tools.sinclair', compact('range', 'graphs'));
    }

    public function invites()
    {
        $user = User::find(Auth::user()->user_id);
        $codes = $user->invite_codes->toArray();
        //$codes = Invite_code::valid(Auth::user()->user_id)->get();
        return view('tools.invites', compact('codes'));
    }
}
