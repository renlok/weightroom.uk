@extends('layouts.master')

@section('title', 'Latest Blog Posts')

@section('headerstyle')
<style>
h3 {
  margin-top: 0px;
}
</style>
@endsection

@section('content')
<h2>Blog</h2>
<ul>
@foreach ($posts as $post)
  <li class="list-group-item">
    <h3>{{ $post->title }}</h3>
    <p>{{ $post->description }}</p>
    <a class="btn btn-default btn-xs" href="{{ route('viewBlogPost', ['url' => $post->url]) }}">Read More</a>
  </li>
@endforeach
</ul>
{!! $posts->render() !!}
@endsection

@section('endjs')
@endsection
