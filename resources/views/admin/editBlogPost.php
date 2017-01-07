@extends('layouts.master')

@section('title', 'Admin: ' . (($blog_id == 0) ? 'Add' : 'Edit') . ' Blog Post')

@section('headerstyle')
<style>
</style>
@endsection

@section('content')
<h2>Admin Land: {{ ($blog_id == 0) ? 'Add' : 'Edit' }} Blog Post</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

@include('common.flash')
<form action="{{ ($blog_id == 0) ? route('adminAddBlogPost') : route('adminEditBlogPost', ['blog_id' => $blog_id]) }}" method="post">
	<input type="hidden" name="blog_id" value="{{ $blog_id }}">
	<label for="blogName">Blog name</label>
	<input type="text" id="blogName" name="blog_name" placeholder="Blog name" value="{{ $blog_name }}">
	<label for="blogDesc">Template description</label>
	<input type="text" id="blogDesc" name="blog_description" placeholder="Blog description" value="{{ $blog_description }}">
  <label for="blogContent">Blog content</label>
	<textarea id="blogContent" name="blog_content">{{ $blog_content }}</textarea>
  <label for="blogPublishedAt">Published at:</label>
	<input type="text" id="blogPublishedAt" name="blog_published_at" placeholder="Published At" value="{{ $blog_published_at }}">
  {!! csrf_field() !!}
	<button type="submit" class="btn btn-default">Submit</button>
</form>
@endsection

@section('endjs')
@endsection
