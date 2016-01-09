<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use App\Comment;
use App\Log;

class CommentController extends Controller
{
	public function store($log_id, Request $request)
	{
		$log = Log::find($log_id)->get();
		Comments::insert([
			'parent_id' => $request->input('parent_id'),
			'comment' => $request->input('comment'),
			'commentable_id' => $log_id,
			'user_id' => Auth::user()->user_id
		]);

		DB::table('notifications')->insert([
            'user_id' => $log->user_id,
            'notification_type' => 'comment',
            'notification_value' => Auth::user()->user_name
        ]);
		$date = $log->log_date;
		return redirect()
                ->route('viewLog', ['date' => $date])
                ->with('commenting', true);
	}
}
