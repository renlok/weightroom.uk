@extends('layouts.master')

@section('title', 'Latest Blog Posts')

@section('headerstyle')
@endsection

@section('content')
<ul class="list-group">
@foreach ($posts as $post)
  <li class="list-group-item">
    <h2>{{ $post->title }}</h2>
    <p>{{ $post->description }}</p>
    <a class="btn btn-primary" href="{{ route('viewBlogPost', ['url' => $post->url]) }}">Read More</a>
  </li>
@endforeach
</ul>
{!! $posts->render() !!}
@endsection

@section('endjs')
@endsection
