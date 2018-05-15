<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use App\Comment;
use App\Log;
use App\Post;
use App\Notification;
use App\Extend\Format;
use Carbon;

class CommentController extends Controller
{
    public function storeLogComment($object_id, Request $request)
    {
        $log = Log::where('log_id', $object_id)->firstOrFail();
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
                $parent_comment = Comment::select('user_id')
                                ->where('comment_id', $request->input('parent_id'))
                                ->withTrashed()
                                ->firstOrFail();
                Notification::create([
                    'user_id' => $parent_comment->user_id,
                    'notification_type' => 'reply',
                    'notification_from' => ['log_date' => $log->log_date->toDateString(), 'user_name' => $log->user->user_name],
                    'notification_value' => Auth::user()->user_name
                ]);
            }
            Comment::create([
                'parent_id' => $request->input('parent_id'),
                'comment' => $request->input('comment'),
                'comment_html' => Format::replace_video_urls(Format::addUserLinks(e($request->input('comment')), 'logcomments', ['url_params' => ['log_date' => $log->log_date->toDateString(), 'user_name' => $log->user->user_name]])),
                'commentable_id' => $object_id,
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

    public function storeBlogComment($object_id, Request $request)
    {
        // check its valid
        $post = Post::where('post_id', $object_id)->firstOrFail();
        if (!ctype_space($request->input('comment')) && !empty($request->input('comment')))
        {
            if ($request->input('parent_id') != 0)
            {
                $parent_comment = Comment::select('user_id')
                                ->where('comment_id', $request->input('parent_id'))
                                ->withTrashed()
                                ->firstOrFail();
                Notification::create([
                    'user_id' => $parent_comment->user_id,
                    'notification_type' => 'replyBlog',
                    'notification_from' => ['post_url' => $post->url],
                    'notification_value' => Auth::user()->user_name
                ]);
            }
            Comment::create([
                'parent_id' => $request->input('parent_id'),
                'comment' => $request->input('comment'),
                'comment_html' => Format::replace_video_urls(Format::addUserLinks(e($request->input('comment')), 'blogcomments', ['url_params' => ['url' => $post->url]])),
                'commentable_id' => $object_id,
                'commentable_type' => 'App\Post',
                'comment_date' => Carbon::now(),
                'user_id' => Auth::user()->user_id,
                'user_name' => Auth::user()->user_name
            ]);
        }
        return redirect()
                ->route('viewBlogPost', ['url' => $post->url])
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
