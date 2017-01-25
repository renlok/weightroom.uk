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
        $log = Log::where('log_id', $log_id)->firstOrFail();
        if (!ctype_space($request->input('comment')) && !empty($request->input('comment')))
        {
            if ($request->input('parent_id') == 0)
            {
                Notification::create([
                    'user_id' => $log->user_id,
                    'notification_type' => 'comment',
                    'notification_from' => ['log_date' => $log->log_date->toDateString()],
                    'notification_value' => Auth::user()->user_name
                ]);
            }
            else
            {
                $parent_comment = Comment::join('logs', 'logs.log_id', '=', 'comments.commentable_id')
                                ->join('users', 'logs.user_id', '=', 'users.user_id')
                                ->select('logs.log_date', 'comments.user_id', 'users.user_name')
                                ->where('comment_id', $request->input('parent_id'))
                                ->withTrashed()
                                ->firstOrFail();
                Notification::create([
                    'user_id' => $parent_comment->user_id,
                    'notification_type' => 'reply',
                    'notification_from' => ['log_date' => $parent_comment->log_date, 'user_name' => $parent_comment->user_name],
                    'notification_value' => Auth::user()->user_name
                ]);
            }
            Comment::create([
                'parent_id' => $request->input('parent_id'),
                'comment' => $request->input('comment'),
                'commentable_id' => $log_id,
                'commentable_type' => 'App\Log',
                'comment_date' => Carbon::now(),
                'user_id' => Auth::user()->user_id,
                'user_name' => Auth::user()->user_name
            ]);
        }
        return redirect()
                ->route('viewLog', ['date' => $log->log_date->toDateString(), 'user_name' => $log->user->user_name])
                ->with('commenting', true);
    }

    public function delete($comment_id)
    {
        $comment = Comment::where('comment_id', $comment_id)->firstOrFail();
        if ($comment->user_id == Auth::user()->user_id)
        {
            Comment::where('comment_id', $comment_id)->delete();
            return 0;
        }
        else
        {
            return abort(403, 'Unauthorized action.');
        }
    }
}
