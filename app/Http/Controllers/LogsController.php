<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class LogsController extends Controller
{
  public function index()
  {
    return $this->view();
  }

  public function view()
  {
    return view('logs.view');
  }

  public function edit()
  {
    return view('logs.edit');
  }

  public function search()
  {
    return view('logs.search');
  }

  public function volume()
  {
    return view('logs.volume');
  }
}
