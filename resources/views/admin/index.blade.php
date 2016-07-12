@extends('layouts.master')

@section('title', 'Admin Home')

@section('content')
<h2>Admin Land</h2>
<p>We love you, you're rad.</p>
<p>Things to click</p>
<ul>
	<li><a href="{{ route('adminStats') }}">User Stats</a></li>
</ul>
@endsection
