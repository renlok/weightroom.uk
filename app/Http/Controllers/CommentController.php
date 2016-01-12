<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use App\Comment;
use App\Log;
use App\Notification;
use Carbon;

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
			'comment_date' => Carbon::now(),
			'user_id' => Auth::user()->user_id
		]);
		Notification::create([
            'user_id' => $log->user_id,
            'notification_type' => 'comment',
			'notification_from' => $log->log_date->toDateString(),
            'notification_value' => Auth::user()->user_name
        ]);
		return redirect()
                ->route('viewLog', ['date' => $log->log_date->toDateString()])
                ->with('commenting', true);
	}

	public function delete($comment_id)
	{
		$comment = Comment::where('comment_id', $comment_id)->get();
		if ($comment->user_id == Auth::user()->user_id)
		{
			Comment::where('comment_id', $comment_id)->delete();
		}
		else
		{
			return abort(403, 'Unauthorized action.');
		}
	}
}
