@extends('layouts.master')

@section('title', 'Goals')

@section('headerstyle')
@endsection

@section('content')
<h2>Goals</h2>

<div class="container">
@foreach($exercise_groups as $exercise_name => $exercise_goals)
	<h3>{{ $exercise_name }}</h3>
	@foreach ($exercise_goals as $goal)
		<div class="padding">
		@if ($goal->goal_type == 'wr')
			<span><b>{{ $goal->goal_value_one }}</b> {{ Auth::user()->user_unit }} x <b>{{ $goal->goal_value_two }}</b></span>
		@elseif ($goal->goal_type == 'rm')
			<span>Estimate 1RM: <b>{{ $goal->goal_value_one }}</b></span>
		@elseif ($goal->goal_type == 'tv')
			<span>Total volume: <b>{{ $goal->goal_value_one }}</b></span>
		@else
			<span>Total Reps: <b>{{ $goal->goal_value_one }}</b></span>
		@endif
		<a href="#">edit</a>
	    <div class="progress">
	      <div class="progress-bar" role="progressbar" aria-valuenow="{{ $goal->percent }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $goal->percent }}%;">
	        {{ $goal->percent }}%
	      </div>
	    </div>
		<div class="edit" id="edit-{{ $goal->goal_id }}">&nbsp;</div>
	</div>
	@endforeach
@endforeach
</div>
@endsection

@section('endjs')
@endsection
