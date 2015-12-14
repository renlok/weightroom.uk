<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ToolsController extends Controller
{
  public function index()
  {
    return view('tools.index');
  }

  public function bodyweight()
  {
    return view('tools.bodyweight');
  }

  public function wilks()
  {
    return view('tools.wilks');
  }

  public function sinclair()
  {
    return view('tools.sinclair');
  }

  public function invites()
  {
    return view('tools.invites');
  }
}
