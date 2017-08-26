<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserSettingsRequest;
use App\Http\Controllers\Controller;
use Auth;
use DB;
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

    public function follow($user_name, $date = null)
    {
        // add follower
        $user = User::select('user_id', 'user_private')->where('user_name', $user_name)->firstOrFail();
        User_follow::create([
            'user_id' => Auth::user()->user_id,
            'follow_user_id' => $user->user_id,
            'is_accepted' => !$user->user_private
        ]);
        Notification::create([
            'user_id' => $user->user_id,
            'notification_type' => 'follow',
            'notification_value' => Auth::user()->user_name
        ]);
        if ($date == null)
        {
            return redirect()
                ->route('followersList')
                ->with('flash_message', 'User followed.');
        }
        else
        {
            return redirect()
                ->route('viewLog', ['user_name' => $user_name, 'date' => $date])
                ->with('flash_message', 'User followed.');
        }
    }

    public function unfollow($user_name, $date = null)
    {
        User_follow::where('user_id', Auth::user()->user_id)
                    ->where('follow_user_id', User::where('user_name', $user_name)->firstOrFail()->user_id)
                    ->delete();
        if ($date == null)
        {
            return redirect()
                  ->route('followingList')
                  ->with('flash_message', 'User unfollowed.');
        }
        else
        {
            return redirect()
                  ->route('viewLog', ['user_name' => $user_name, 'date' => $date])
                  ->with('flash_message', 'User unfollowed.');
        }
    }

    public function getAcceptUserFollow($user_name)
    {
        $user_id = User::where('user_name', $user_name)->firstOrFail()->user_id;
        User_follow::where('is_accepted', 0)
                    ->where('follow_user_id', Auth::user()->user_id)
                    ->where('user_id', $user_id)
                    ->update(['is_accepted' => 1]);
        return redirect()
            ->route('followersList')
            ->with('flash_message', 'User accepted.');
    }

    public function getFollowingList()
    {
        $followed_users = User_follow::with('user')->where('user_id', Auth::user()->user_id)->paginate(50);
        $list_type = 'followings';
        return view('user.followList', compact('followed_users', 'list_type'));
    }

    public function getFollowersList(Request $request)
    {
        $take = $request->input("per_page", 50);
        $page = $request->input("page", 1);
        $skip = $page * $take;
        if($take < 1) { $take = 1; }
        if($skip < 0) { $skip = 0; }

        $followed_users = DB::select(DB::raw("SELECT uf.is_accepted, u.user_name, uf.created_at,
          (SELECT COUNT(*) As is_following FROM user_follows uf0 WHERE uf0.user_id = uf.follow_user_id AND uf.user_id = uf0.follow_user_id) As is_following
          FROM user_follows uf
          LEFT JOIN users u ON (uf.user_id = u.user_id)
          WHERE uf.follow_user_id = :follow_user_id"), ["follow_user_id" => Auth::user()->user_id]);
        $totalCount = count($followed_users);
        $results = array_slice($followed_users, $skip, $take);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($results, $totalCount, $take, $page);

        return view('user.followersList', compact('followed_users', 'paginator'));
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

    public function clearNotification($note_id)
    {
        Notification::where('user_id', Auth::user()->user_id)->where('notification_id', $note_id)->delete();
        return redirect()
                ->back()
                ->with('flash_message', 'Notification deleted.');
    }
}
