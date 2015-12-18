<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function search()
    {
      return view('user.search');
    }

    public function settings()
    {
      return view('user.settings');
    }
}
