<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Exercise;

class ExercisesController extends Controller
{
    public function list()
    {
        $exercises = Exercise->listexercises(true)->get();
        return view('exercise.list', compact('exercises'));
    }

    public function getEdit()
    {
        return view('exercise.edit');
    }

    public function postEdit()
    {
        return view('exercise.edit');
    }

    public function history()
    {
        return view('exercise.history');
    }

    public function volume()
    {
        return view('exercise.volume');
    }
}
