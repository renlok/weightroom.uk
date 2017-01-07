@extends('layouts.master')

@section('title', $post->title)

@section('headerstyle')
@endsection

@section('content')
<div class="container">
  <div class="single-blog-post">
    <h3>{{ $post->title }}</h3>
    <p>Posted at: {{ $post->published_at->format('d M, Y - h:i:s') }}</p>
    <p>{{ $post->content }}</p>
    <div class="pull-right" style="margin-top: 20px;">
      <div class="btn-group btn-group-sm" role="group" aria-label="type">
        <a href="{{ $prev_url }}" class="btn btn-default">Previous</a>
        <a href="{{ $next_url }}" class="btn btn-default">Next</a>
      </div>
    </div>
  </div>
</div>
@endsection

@section('endjs')
@endsection
