<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MiscController extends Controller
{
  public function index()
  {
    return view('misc.volume');
  }

  public function ajax()
  {
    //
  }

  public function demo()
  {
    return view('misc.demo');
  }

  public function dash()
  {
    return view('misc.dash');
  }
}
