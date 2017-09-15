@extends('layouts.master')

@section('title', 'Admin Home')

@section('content')
<h2>Admin Land</h2>
@include('common.flash')
<p>We love you, you're rad.</p>
<h3>Stuff</h3>
<ul>
	<li><a href="{{ route('adminListUsers') }}">List Users</a></li>
	<li><a href="{{ route('adminStats') }}">User Stats</a></li>
	<li><a href="{{ route('adminViewLogs') }}">Error Logs</a></li>
	<li><a href="{{ route('adminSettings') }}">Settings</a></li>
</ul>
<h3>Workout Templates</h3>
<ul>
	<li><a href="{{ route('adminListTemplates') }}">List Templates</a></li>
	<li><a href="{{ route('adminAddTemplate') }}">Add Workout Template</a></li>
</ul>
<h3>Blog</h3>
<ul>
	<li><a href="{{ route('adminListBlogPosts') }}">List Blog Posts</a></li>
	<li><a href="{{ route('adminAddBlogPost') }}">Add Blog Post</a></li>
</ul>
<h3>Force cron tasks</h3>
<ul>
	<li><a href="{{ route('forceStats') }}">Force Stats Count</a></li>
	<li><a href="{{ route('cleanJunk') }}">Force Clean Junk</a></li>
	<li><a href="{{ route('cronImport') }}">Force Cron Import</a><span class="badge"> {{ $cron_count }}</span></li>
</ul>
@endsection
