<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\Invite_code;
use App\Log;

class ToolsController extends Controller
{
    public function index()
    {
        return view('tools.index');
    }

    public function bodyweight($range = 0)
    {
        $graphs = [];
        $graphs['Bodyweight'] = Log::select('log_date', 'log_weight')
                                        ->where('user_id', Auth::user()->user_id)
                                        ->where('log_weight', '!=', 0)
                                        ->orderBy('log_date', 'asc')
                                        ->get();
        return view('tools.bodyweight', compact('range', 'graphs'));
    }

    public function wilks($range = 0)
    {
        return view('tools.wilks', compact('range'));
    }

    public function sinclair($range = 0)
    {
        return view('tools.sinclair', compact('range'));
    }

    public function invites()
    {
        $codes = Invite_code::valid(Auth::user()->user_id)->get();
        return view('tools.invites', compact('codes'));
    }
}
