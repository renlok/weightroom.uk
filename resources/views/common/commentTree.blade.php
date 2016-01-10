<a name="comments"></a>
<ul class="log_comments">
@foreach ($comments as $comment)
<li>
	<div class="comment">
		<h6>
			<a href="{{ route('viewUser', ['user_name' => $comment->user_name]) }}">{{ $comment->user_name }}</a>
			<small>{{ $comment->comment_date->diffInDays() }}</small>
		</h6>
		{{ $comment->comment }}
		<p class="small"><a href="#" class="reply">reply</a></p>
		<div class="comment-reply-box" style="display:none;">
			@include('common.commentForm', ['parent_id' => $comment->comment_id])
		</div>
		@include('common.commentChild', ['children_comments' => $comment->children()])
	</div>
</li>
@endforeach
</ul>
@include('common.commentForm', ['parent_id' => 0])
