@extends('layouts.master')

@section('title', 'Workout Templates')

@section('content')
<h2>Workout Templates</h2>
<p>Preset workouts for you to browse</p>
@include('common.beta')

@foreach ($template_groups as $group_name => $template_group)
	<h2>{{ ucwords($group_name) }}</h2>
	@foreach ($template_group as $tempalte)
	<ul>
		<li><a href="{{ route('viewTemplate', ['template_id' => $tempalte->template_id]) }}">{{ $tempalte->template_name }}</a></li>
	</ul>
	@endforeach
@endforeach
@endsection
