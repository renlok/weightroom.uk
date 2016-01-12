<?php

namespace App\Http\Controllers;

use User_follow;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function demo()
    {
        return view('demo');
    }

    public function privacyPolicy()
    {
        return view('help.privacypolicy');
    }

    public function dash()
    {
        return view('dash');
    }

    public function dashLogs()
    {
        User_follow::where('user_id', Auth::user()->user_id)->user()->logs()->get();
        return view('dash');
    }

    public function dashAll()
    {
        return view('dash');
    }
}
