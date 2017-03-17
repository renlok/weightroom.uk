@if (${"children_comments$token"} != null)
	<ul class="comment_child">
	@foreach (${"children_comments$token"} as ${"comment$token"})
	<li>
		<div class="comment">
			<h6><a href="{{ route('viewUser', ['user_name' => ${"comment$token"}->user_name]) }}">{{ ${"comment$token"}->user_name }}</a> <small>{{ ${"comment$token"}->comment_date->diffForHumans() }}</small></h6>
			@if (!${"comment$token"}->trashed())
				<span id="c{{ ${"comment$token"}->comment_id }}">{{ ${"comment$token"}->comment }}</span>
				<p class="small"><a href="#" class="reply">reply</a> <a href="#" class="delete" c-id="{{ ${"comment$token"}->comment_id }}">delete</a></p>
				<div class="comment-reply-box" style="display:none;">
					@include('comments.commentForm', ['parent_id' => ${"comment$token"}->comment_id])
				</div>
			@else
				[Deleted]
			@endif
			@include('comments.commentChild', ['children_comments' . ($token + 1) => ${"comment$token"}->children()->get(), 'token' => ($token + 1)])
		</div>
	</li>
	@endforeach
	</ul>
@endif
