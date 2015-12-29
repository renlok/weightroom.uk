<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Exercise;
use App\Logs;
use App\Extends\PRs;

class ExercisesController extends Controller
{
    public function list()
    {
        $exercises = Exercise::listexercises(true)->get();
        return view('exercise.list', compact('exercises'));
    }

    public function getEdit($exercise_name, Request $request)
    {
        return view('exercise.edit', compact('exercise_name'));
    }

    public function postEdit($exercise_name)
    {
        $new_name = $request->input('exercisenew');
        $exercise_old = Exercise::where('exercise_name', $exercise_name)->first();
        $exercise_new = Exercise::where('exercise_name', $new_name)->first();
        // new name already exists
        if($exercise_new->count() > 0)
    	{
            $final_id = $exercise_new->$exercise_id;
            // remove the old exercise and merge it with an exsisting one
    		// update the exercise id
            DB::table('log_exercises')
                ->where('exercise_id', $exercise_old->$exercise_id)
                ->update(['exercise_id' => $exercise_new->$exercise_id]);
    		// update PRs
    		PRs::rebuildExercisePRs($exercise_new->$exercise_id);
    		// delete the old PRs
            DB::table('exercise_records')
                ->where('exercise_id', $exercise_old->$exercise_id)
                ->delete();
    		// delete the old exercise
            $exercise_old->delete();
    	}
    	else
    	{
            $final_id = $exercise_old->$exercise_id;
    		// rename the exercise
            $exercise_old->exercise_name = $new_name;
            $exercise_old->save();
    	}
    	// update the log texts
        DB::table('logs')
            ->join('log_exercises', 'logs.log_id', '=', 'log_exercises.log_id')
            ->where('logs.user_id', Auth::user()->user_id)
            ->where('log_exercises.exercise_id', $final_id)
            ->update(['logs.log_update_text' => 1]);
        return redirect()
            ->route('viewExercise', ['exercise_name' => $exercise_name]);
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

    public function compare($exercise1, $exercise2, $exercise3, $exercise4, $exercise5)
    {

    }
}
