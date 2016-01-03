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
		Comments::insert([
			'parent_id' => $request->input('parent_id'),
			'comment' => $request->input('comment'),
			'commentable_id' => $log_id,
			'user_id' => Auth::user()->user_id
		]);
		$date = Log::find($log_id)->value('log_date');
		return redirect()
                ->route('viewLog', ['date' => $date])
                ->with('commenting', true);
	}
}
