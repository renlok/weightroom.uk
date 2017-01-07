@extends('layouts.master')

@section('title', 'Admin: List Blog Posts')

@section('headerstyle')
<style>
</style>
@endsection

@section('content')
<h2>Admin Land: List Blog Posts</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

@include('common.flash')
<ul class="list-group">
  <li class="list-group-item"><a href="{{ route('adminAddBlogPost') }}">Add Post</a></li>
@foreach ($posts as $post)
  <li class="list-group-item"><a href="{{ route('adminEditBlogPost', ['post_id' => $post->post_id]) }}">{{ $post->title }}</a></li>
@endforeach
</ul>
@endsection

@section('endjs')
@endsection
