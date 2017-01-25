<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Exercise;
use App\Exercise_goal;

class GoalController extends Controller
{
    public function getGlobalGoals()
    {
        $exercise_groups = Exercise_goal::select('exercises.exercise_name', DB::raw('exercise_goals.*'))
                ->join('exercises', 'exercises.exercise_id', '=', 'exercise_goals.exercise_id')
                ->where('exercise_goals.user_id', Auth::user()->user_id)->get();
        $exercise_groups = $exercise_groups->groupBy('exercise_name');
        $exercises = Exercise::listexercises(true)->get();
        return view('exercise.goals', compact('exercise_groups', 'exercises'));
    }

    public function postNewGoal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'valueOne' => 'required|numeric',
            'goalType' => 'required|in:wr,rm,tv,tr',
            'exerciseId' => 'exists:exercises,exercise_id,user_id,' . Auth::user()->user_id
        ]);
        if ($validator->fails()) {
            return redirect()
                    ->route('globalGoals')
                    ->withErrors($validator)
                    ->withInput();
        }
        Exercise_goal::insert([
            'user_id' => Auth::user()->user_id,
            'exercise_id' => $request->input('exerciseId'),
            'goal_type' => $request->input('goalType'),
            'goal_value_one' => $request->input('valueOne'),
            'goal_value_two' => $request->input('valueTwo')
        ]);
        return redirect()
            ->route('globalGoals')
            ->with('flash_message', 'Goal Added.');
    }
    public function postUpdateExerciseGoals($exercise_name, Request $request)
    {
        $exercise = Exercise::select('exercise_id')->where('exercise_name', $exercise_name)->where('user_id', Auth::user()->user_id)->firstOrFail();
        // deal with new goal if entered
        if ($request->has('valueOne'))
        {
            Exercise_goal::insert([
                'user_id' => Auth::user()->user_id,
                'exercise_id' => $exercise->exercise_id,
                'goal_type' => $request->input('goalType'),
                'goal_value_one' => $request->input('valueOne'),
                'goal_value_two' => $request->input('valueTwo')
            ]);
        }
        if ($request->has('editGoalType'))
        {
            foreach($request->input('editGoalType') as $goal_id => $goal_type)
            {
                Exercise_goal::where('goal_id', $goal_id)
                    ->where('user_id', Auth::user()->user_id)
                    ->where('exercise_id', $exercise->exercise_id)
                    ->update([
                        'goal_type' => $goal_type,
                        'goal_value_one' => $request->input('editValueOne')[$goal_id],
                        'goal_value_two' => $request->input('editValueTwo')[$goal_id]
                    ]);
            }
        }
        return redirect()
            ->route('viewExercise', ['exercise_name' => $exercise_name])
            ->with(['flash_message' => "$exercise_name goals have been updated"]);
    }

    public function postDeleteGoal(Request $request)
    {
        $query = Exercise_goal::where('goal_id', $request->input('id'))
                ->where('user_id', Auth::user()->user_id);
        if ($query->first() == null)
            abort(404);
        else
        {
            $query->delete();
            return redirect()
                ->route('globalGoals')
                ->with('flash_message', 'Goal Deleted.');
        }
    }

    public function postUpdateGoal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'editValueOne' => 'required|numeric',
            'goalType' => 'required|in:wr,rm,tv,tr',
            'editGoalID' => 'exists:exercise_goals,goal_id,user_id,' . Auth::user()->user_id
        ]);
        if ($validator->fails()) {
            return redirect()
                    ->route('globalGoals')
                    ->withErrors($validator)
                    ->withInput();
        }
        Exercise_goal::where('goal_id', $request->input('editGoalID'))
            ->where('user_id', Auth::user()->user_id)
            ->update([
                'goal_type' => $request->input('goalType'),
                'goal_value_one' => $request->input('editValueOne'),
                'goal_value_two' => $request->input('editValueTwo')
            ]);
        return redirect()
            ->route('globalGoals')
            ->with('flash_message', 'Goal Updated.');
    }
}
