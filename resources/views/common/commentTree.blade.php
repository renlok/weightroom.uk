<a name="comments"></a>
<ul class="log_comments">
@foreach ($comments as $comment)
<li>
	<div class="comment">
		<h6><a href="{{ route('viewUser', ['user' => $comment->sender_user_id]) }}">{{ $comment->user_name }}</a> <small>{{ $comment->comment_date->diffInDays(Carbon::now()) }}</small></h6>
		{{ $comment->comment }}
		<p class="small"><a href="#" class="reply">reply</a></p>
		<div class="comment-reply-box" style="display:none;">
			@import('common.commentForm', ['parent_id' => $comment->comment_id])
		</div>
		@import('common.commentChild', ['children_comments' => $comment->children()])
	</div>
</li>
@endforeach
</ul>
@import('common.commentForm', ['parent_id' => 0])