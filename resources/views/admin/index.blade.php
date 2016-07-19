@extends('layouts.master')

@section('title', 'Admin Home')

@section('content')
<h2>Admin Land</h2>
<p>We love you, you're rad.</p>
<p>Things to click</p>
<ul>
	<li><a href="{{ route('adminStats') }}">User Stats</a></li>
	<li><a href="{{ route('adminSettings') }}">Settings</a></li>
	<li><a href="{{ route('cronImport') }}">Force Cron Import</a><span class="badge">{{ $cron_count }}</span></li>
	
</ul>
@endsection
