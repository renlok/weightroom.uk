@extends('layouts.master')

@section('title', $post->title)

@section('headerstyle')
@include('comments.commentCss')
@endsection

@section('content')
<h2>Blog</h2>
<div class="container">
  <div class="single-blog-post">
    <h3>{{ $post->title }}</h3>
    <p>Posted at: {{ $post->published_at->format('d M, Y - h:i:s') }}</p>
    <p>{!! $post->content !!}</p>
    <div class="pull-right" style="margin-top: 20px;">
      <div class="btn-group btn-group-sm" role="group" aria-label="type">
        <a href="{{ route('viewBlogPost', ['url' => $prev_url]) }}" class="btn btn-default"{{ ($prev_url == '#') ? ' disabled="disabled"' : '' }}>Previous</a>
        <a href="{{ route('viewBlog') }}" class="btn btn-default">Blog Home</a>
        <a href="{{ route('viewBlogPost', ['url' => $next_url]) }}" class="btn btn-default"{{ ($next_url == '#') ? ' disabled="disabled"' : '' }}>Next</a>
      </div>
    </div>
  </div>
  @include('comments.commentTree', ['comments' => $comments, 'object_id' => $post->post_id, 'object_type' => 'Blog'])
</div>
@endsection

@section('endjs')
@include('comments.commentJs')
@endsection
