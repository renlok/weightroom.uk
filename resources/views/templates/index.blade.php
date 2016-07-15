@extends('layouts.master')

@section('title', 'Workout Templates')

@section('content')
<h2>Workout Templates</h2>
<p>Preset workouts for you to browse</p>
@include('common.beta')

@foreach ($templates as $tempalte)
<ul>
	<li><a href="{{ route('viewTemplate', ['template_id' => $tempalte->template_id]) }}">{{ $tempalte->template_name }}</a></li>
</ul>
@endforeach
@endsection
