<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserSettingsRequest;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\Exercise;
use App\User_follow;
use App\Notification;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $users = User::userlike($request->input('username'));
        return view('user.search', compact('users'));
    }

    public function follow($user_name, $date)
    {
        // add follower
        $user_id = User::where('user_name', $user_name)->firstOrFail()->user_id;
        User_follow::create([
            'user_id' => Auth::user()->user_id,
            'follow_user_id' => $user_id
        ]);
        Notification::create([
            'user_id' => $user_id,
            'notification_type' => 'follow',
            'notification_value' => Auth::user()->user_name
        ]);
        return redirect()
                ->route('viewLog', ['user_name' => $user_name, 'date' => $date]);
    }

    public function unfollow($user_name, $date)
    {
        User_follow::where('user_id', Auth::user()->user_id)
                    ->where('follow_user_id', User::where('user_name', $user_name)->firstOrFail()->user_id)
                    ->delete();
        return redirect()
                ->route('viewLog', ['user_name' => $user_name, 'date' => $date]);
    }

    public function getSettings()
    {
        $user = Auth::user();
        $exercises = Exercise::listexercises(false)->get();
        return view('user.settings', compact('user', 'exercises'));
    }

    public function postSettings(UpdateUserSettingsRequest $request)
    {
        //build temporary new data
        $user = Auth::user();
        $user->user_unit = $request->input('weightunit');
        $user->user_showreps = $request->input('showreps');
        $user->user_showextrareps = array_map('trim', explode(',', $request->input('showextrareps')));
        $user->user_squatid = $request->input('squat');
        $user->user_deadliftid = $request->input('deadlift');
        $user->user_benchid = $request->input('bench');
        $user->user_snatchid = $request->input('snatch');
        $user->user_cleanjerkid = $request->input('cnj');
        $user->user_weight = $request->input('bodyweight');
        $user->user_gender = $request->input('gender');
        $user->user_volumeincfails = $request->input('volumeincfails');
        $user->user_weekstart = $request->input('weekstart');
        $user->user_showintensity = $request->input('viewintensityabs');
        $user->user_limitintensity = $request->input('limitintensity');
        $user->user_limitintensitywarmup = $request->input('limitintensitywarmup');
        $user->user_showinol = $request->input('showinol');
        $user->user_inolincwarmup = $request->input('inolincwarmup');
        $user->user_volumeincwarmup = $request->input('volumeincwarmup');
        $user->user_private = $request->input('privacy');
        $user->save();
        return redirect()
                ->route('userSettings')
                ->withInput()
                ->with('flash_message', 'Settings updated.');
    }

    public function clearNotifications()
    {
        Notification::where('user_id', Auth::user()->user_id)->delete();
        return redirect()
                ->back()
                ->with('flash_message', 'Notifications cleared.');
    }
}
