@extends('layouts.master')

@section('title', 'Workout Templates')

@section('content')
<h2>Workout Templates</h2>
<p>Preset workouts for you to browse</p>
<div class="alert alert-danger" role="alert">The templates are super beta a.k.a. all bugs no good. If you have any ideas how to improve them, have found a bug or have something you would like adding as a template send a message to me on <a href="https://www.reddit.com/message/compose/?to=racken">reddit</a></div>

@foreach ($templates as $tempalte)
<ul>
	<li><a href="{{ route('viewTemplate', ['template_id' => $tempalte->template_id]) }}">{{ $tempalte->template_name }}</a></li>
</ul>
@endforeach
@endsection
