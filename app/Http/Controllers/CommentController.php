<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use App\Comment;
use App\Log;
use App\Notification;

class CommentController extends Controller
{
	public function store($log_id, Request $request)
	{
		$log = Log::where('log_id', $log_id)->first();
		Comment::create([
			'parent_id' => $request->input('parent_id'),
			'comment' => $request->input('comment'),
			'commentable_id' => $log_id,
			'commentable_type' => 'App\Log',
			'user_id' => Auth::user()->user_id
		]);
		Notification::create([
            'user_id' => $log->user_id,
            'notification_type' => 'comment',
			'notification_from' => $log->log_date,
            'notification_value' => Auth::user()->user_name
        ]);
		$date = $log->log_date;
		return redirect()
                ->route('viewLog', ['date' => $date->toDateString()])
                ->with('commenting', true);
	}
}
