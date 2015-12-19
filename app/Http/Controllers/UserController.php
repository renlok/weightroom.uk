<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\User;

class UserController extends Controller
{
    public function search()
    {
      return view('user.search');
    }

    public function getSettings()
    {
        $user = Auth::user();
        return view('user.settings', compact('user'));
    }

    public function postSettings()
    {
        $user = Auth::user();
        return view('user.settings', compact('user'));
    }
}
