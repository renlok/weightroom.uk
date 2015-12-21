<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\UpdateUserSettingsRequest;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\Exercise;
use App\Invite_code;

class UserController extends Controller
{
    public function search()
    {
        return view('user.search');
    }

    public function follow($user_name, $date)
    {
        $invite_code = new Invite_code();
        //$invite_code->user_id = Auth::user()->user_id;
        $invite_code->follow_user_id = User::where('user_name', $user_name)->firstOrFail()->user_id;
        Auth::user()->invite_codes()->save($invite_code);
        return redirect('viewLog', ['user' => $user_name, 'date' => $date]);
    }

    public function unfollow($user_name, $date)
    {
        Invite_code()::where('user_id', Auth::user()->user_id)
                    ->where('follow_user_id', User::where('user_name', $user_name)->firstOrFail()->user_id)
                    ->delete();
        return redirect('viewLog', ['user' => $user_name, 'date' => $date]);
    }

    public function getSettings()
    {
        $user = Auth::user();
        $showreps = [];
        for ($i = 1; $i <= 10; $i++)
        {
        	$showreps[$i] = (isset($user->user_showreps[$i])) ? ' checked' : '';
        }
        $exercises = Exercise->listexercises(false)->get();
        $settings_updated = false;
        return view('user.settings', compact('user', 'showreps', 'exercises', 'settings_updated'));
    }

    public function postSettings(UpdateUserSettingsRequest $request)
    {
        $settings_updated = true;

        //build temporary new data
        $user = User::find(Auth::user()->user_id);
        $user->user_unit = $request->input('weightunit');
        $user_showreps = $request->input('showreps.*');
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
        $user->save();

        $showreps = [];
        for ($i = 1; $i <= 10; $i++)
        {
        	$showreps[$i] = (isset($user->user_showreps[$i])) ? ' checked' : '';
        }
        $exercises = Exercise->listexercises(false)->get();
        $settings_updated = false;

        return view('user.settings', compact('user', 'errors', 'showreps', 'exercises', 'settings_updated'));
    }
}
