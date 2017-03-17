<a name="comments"></a>
<ul class="object_comments">
@foreach ($comments as $comment)
<li>
	<div class="comment">
		<h6>
			<a href="{{ route('viewUser', ['user_name' => $comment->user_name]) }}">{{ $comment->user_name }}</a>
			<small>{{ $comment->comment_date->diffForHumans() }}</small>
		</h6>
		@if (!$comment->trashed())
			<span id="c{{ $comment->comment_id }}">{{ $comment->comment }}</span>
			<p class="small"><a href="#" class="reply">reply</a> <a href="#" class="delete" c-id="{{ $comment->comment_id }}">delete</a></p>
			<div class="comment-reply-box" style="display:none;">
				@include('comments.commentForm', ['parent_id' => $comment->comment_id])
			</div>
		@else
			[Deleted]
		@endif
		@include('comments.commentChild', ['children_comments0' => $comment->children()->get(), 'token' => 0])
	</div>
</li>
@endforeach
</ul>
@include('comments.commentForm', ['parent_id' => 0])
