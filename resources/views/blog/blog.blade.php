@extends('layouts.master')

@section('title', 'Latest Blog Posts')

@section('headerstyle')
@endsection

@section('content')
<div class="container">
@foreach ($posts as $post)
  <div class="single-blog-post">
    <h2>{{ $post->title }}</h2>
    <p>{{ Markdown::convertToHtml(str_limit($post->content, 200)) }}</p>
    <a class="btn btn-primary" href="{{ route('viewBlogPost', ['url' => $post->url]) }}">Read More</a>
  </div>
@endforeach
</div>
@endsection

@section('endjs')
@endsection
