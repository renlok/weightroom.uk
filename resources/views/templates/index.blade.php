@extends('layouts.master')

@section('title', 'Workout Templates')

@section('content')
<h2>Workout Templates</h2>
<p>Preset workouts for you to browse</p>

@foreach ($templates as $tempalte)
<ul>
	<li><a href="{{ route('viewTemplate', ['template_id' => $tempalte->template_id]) }}">{{ $tempalte->template_name }}</a></li>
</ul>
@endforeach
@endsection
