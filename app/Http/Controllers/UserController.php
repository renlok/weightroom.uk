<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\Exercise;
use Validator;

class UserController extends Controller
{
    public function search()
    {
      return view('user.search');
    }

    public function getSettings()
    {
        $user = Auth::user();
        $user_showreps = array_flip(explode('|', $user->user_showreps));
        $showreps = [];
        for ($i = 1; $i <= 10; $i++)
        {
        	$showreps[$i] = (isset($user_showreps[$i])) ? ' checked' : '';
        }
        $exercises = Exercise->listexercises(false)->get();
        $settings_updated = false;
        return view('user.settings', compact('user', 'showreps', 'exercises', 'settings_updated'));
    }

    public function postSettings(Request $request)
    {
        $old_user = Auth::user();
        $settings_updated = true;

        $validator = Validator::make($request->all(), [
            'gender' => 'required|unique:posts|max:255',
            'bodyweight' => 'required|numeric',
            'weightunit' => 'required|in:kg,lb',
            'weekstart' => 'required|boolean',
            'showreps.*' => 'required',
            'squat' => 'required|exists:exercises,exercise_id,user_id,'.$old_user->user_id,
            'bench' => 'required|exists:exercises,exercise_id,user_id,'.$old_user->user_id,
            'deadlift' => 'required|exists:exercises,exercise_id,user_id,'.$old_user->user_id,
            'snatch' => 'required|exists:exercises,exercise_id,user_id,'.$old_user->user_id,
            'cnj' => 'required|exists:exercises,exercise_id,user_id,'.$old_user->user_id,
            'volumeincfails' => 'required|boolean',
            'viewintensityabs' => 'required|in:p,a,h',
            'limitintensity' => 'required|between:0,100',
        ]);

        //build temporary new data
        $user = User::find($old_user->user_id);
        $user->user_unit = $request->input('weightunit');
        $user_showreps = implode('|', array_map('intval', $request->input('showreps.*')));
        $user->user_showreps = $user_showreps;
        $user->user_squatid = $request->input('squat');
        $user->user_deadliftid = $request->input('deadlift');
        $user->user_benchid = $request->input('bench');
        $user->user_snatchid = $request->input('snatch');
        $user->user_cleanjerkid = $request->input('cnj');
        $user->user_weight = $request->input('bodyweight');
        $user->user_gender = $request->input('gender');
        $user->user_volumeincfails = $request->input('volumeincfails');
        $user->user_weekstart = $request->input('weekstart');
        $user->user_viewintensityabs = $request->input('viewintensityabs');
        $user->user_limitintensity = $request->input('limitintensity');

        $errors = [];
        if ($validator->fails()) {
            $errors = $validator->errors();
        } else {
            // save th new data
            $user->save();
        }

        $user_showreps = array_flip(explode('|', $user->user_showreps));
        $showreps = [];
        for ($i = 1; $i <= 10; $i++)
        {
        	$showreps[$i] = (isset($user_showreps[$i])) ? ' checked' : '';
        }
        $exercises = Exercise->listexercises(false)->get();
        $settings_updated = false;

        return view('user.settings', compact('user', 'errors', 'showreps', 'exercises', 'settings_updated'));
    }
}
