@extends('layouts.master')

@section('title', 'Latest Blog Posts')

@section('headerstyle')
@endsection

@section('content')
<div class="container">
@foreach ($posts as $post)
  <div class="single-blog-post">
    <h2>{{ $post->title }}</h2>
    <p>{{ $post->description }}</p>
    <a class="btn btn-primary" href="{{ route('viewBlogPost', ['url' => $post->url]) }}">Read More</a>
  </div>
@endforeach
{!! $posts->render() !!}
</div>
@endsection

@section('endjs')
@endsection
