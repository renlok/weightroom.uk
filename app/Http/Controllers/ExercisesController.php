<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Exercise;
use App\Log_exercises;

class ExercisesController extends Controller
{
    public function list()
    {
        $exercises = Exercise::listexercises(true)->get();
        return view('exercise.list', compact('exercises'));
    }

    public function getEdit($exercise_name)
    {
        return view('exercise.edit');
    }

    public function postEdit($exercise_name)
    {
        return view('exercise.edit');
    }

    public function history($exercise_name)
    {
        $exercise = Exercise::where('exercise_name', $exercise_name)
                    ->where('user_id', Auth::user()->user_id)->firstOrFail();
        $log_exercises = $exercise->log_exercises();
        return view('exercise.history', compact('exercise_name', 'log_exercises'));
    }

    public function volume($exercise_name)
    {
        return view('exercise.volume');
    }
}
