<?php

namespace App\Http\Controllers;

use Auth;
use Cache;
use DB;
use Validator;
use App\Log;
use App\User;
use App\User_follow;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function demo()
    {
        return view('demo');
    }

    public function plans()
    {
        return view('plans');
    }

    public function faq()
    {
        return view('help.faq');
    }

    public function privacyPolicy()
    {
        return view('help.privacypolicy');
    }

    public function termsOfService()
    {
        return view('help.termsofservice');
    }

    public function getContactUs()
    {
        return view('help.contact');
    }

    public function postContactUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()
                    ->route('contactUs')
                    ->withErrors($validator)
                    ->withInput();
        }
        \Mail::send('emails.contact',
            [
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'user_message' => $request->get('message')
            ], function($message) use ($request)
        {
            $message->from($request->get('email'));
            $message->to('chris@weightroom.uk', 'Admin')->subject('WeightRoom Feedback');
        });
        return redirect()
            ->route('contactUs')
            ->with('flash_message', 'Thanks for contacting us! We will get in touch soon.');
    }

    public function dash()
    {
        $followed_users = User_follow::where('user_id', Auth::user()->user_id)->pluck('follow_user_id');
        $random = false;
        if ($followed_users == null || $followed_users->count() == 0)
        {
            $followed_users = Cache::remember('random_users_dash', 360, function()
            {
                return User::select(DB::raw('DISTINCT(users.user_id)'))->join('logs', 'users.user_id', '=', 'logs.user_id')->orderBy(\DB::raw('RAND()'))->take(10)->pluck('users.user_id');
            });
            $random = true;
            $follow_count = 0;
        }
        else
        {
            $follow_count = $followed_users->count();
        }
        $logs = Log::whereIn('user_id', $followed_users)->whereRaw("TRIM(log_text) != ''")->orderBy('log_date', 'desc')->orderBy('created_at', 'desc')->paginate(50);
        return view('dash', compact('logs', 'random', 'follow_count'));
    }

    public function dashAll()
    {
        $logs = Log::whereRaw("TRIM(log_text) != ''")->orderBy('log_date', 'desc')->orderBy('created_at', 'desc')->paginate(50);
        $random = false;
        $follow_count = 0;
        return view('dash', compact('logs', 'random', 'follow_count'));
    }
}
