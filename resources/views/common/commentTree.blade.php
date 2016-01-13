<a name="comments"></a>
<ul class="log_comments">
@foreach ($comments as $comment)
<li>
	<div class="comment">
		<h6>
			<a href="{{ route('viewUser', ['user_name' => $comment->user_name]) }}">{{ $comment->user_name }}</a>
			<small>{{ $comment->comment_date->diffForHumans() }}</small>
		</h6>
		@if (!$comment->trashed())
			<span id="c{{ $comment->comment_id }}">{{ $comment->comment }}</span>
		@else
			[Deleted]
		@endif
		<p class="small"><a href="#" class="reply">reply</a> <a href="#" class="delete" c-id="{{ $comment->comment_id }}">delete</a></p>
		<div class="comment-reply-box" style="display:none;">
			@include('common.commentForm', ['parent_id' => $comment->comment_id])
		</div>
		@include('common.commentChild', ['children_comments0' => $comment->children()->get(), 'token' => 0])
	</div>
</li>
@endforeach
</ul>
@include('common.commentForm', ['parent_id' => 0])
