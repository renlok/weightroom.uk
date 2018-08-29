@extends('layouts.master')

@section('title', 'Latest Blog Posts')

@section('headerstyle')
<style>
    h3 {
      margin-top: 0;
    }
    .blog-post {
        font-size: 95%;
        padding: 5px 10px;
        margin: 10px;
        max-height: 300px;
        overflow: hidden;;
    }
    .blog-wrapper {
        padding: 10px 0;
    }
</style>
@endsection

@section('content')
<h2>Blog</h2>
<div class="container">
@foreach ($posts as $post)
  <div class="blog-wrapper">
    <h3>{{ $post->title }}</h3>
    <div class="blog-post">{!! $post->content !!}</div>
    <a class="btn btn-default btn-xs" href="{{ route('viewBlogPost', ['url' => $post->url]) }}">Read More</a>
  </div>
@endforeach
{!! $posts->render() !!}
</div>
@endsection

@section('endjs')
@endsection
