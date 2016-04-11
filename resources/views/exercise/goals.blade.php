@extends('layouts.master')

@section('title', 'Goals')

@section('headerstyle')
@endsection

@section('content')
<h2>Goals</h2>
@include('errors.validation')
@include('common.flash')

<div class="container">
@foreach($exercise_groups as $exercise_name => $exercise_goals)
	<h3>{{ ucwords($exercise_name) }}</h3>
	@foreach ($exercise_goals as $goal)
		<div class="padding">
		@if ($goal->goal_type == 'wr')
			<span><b>{{ $goal->goal_value_one }}</b> {{ Auth::user()->user_unit }} x <b>{{ $goal->goal_value_two }}</b></span>
		@elseif ($goal->goal_type == 'rm')
			<span>Estimate 1RM: <b>{{ $goal->goal_value_one }}</b> {{ Auth::user()->user_unit }}</span>
		@elseif ($goal->goal_type == 'tv')
			<span>Total volume: <b>{{ $goal->goal_value_one }}</b> {{ Auth::user()->user_unit }}</span>
		@else
			<span>Total Reps: <b>{{ $goal->goal_value_one }}</b></span>
		@endif
		<a href="#">edit</a>
	    <div class="progress">
	      <div class="progress-bar" role="progressbar" aria-valuenow="{{ $goal->percentage }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $goal->percentage }}%;">
	        {{ $goal->percentage }}%: {{ $goal->best }}
	      </div>
	    </div>
		<div class="edit" id="edit-{{ $goal->goal_id }}">&nbsp;</div>
	</div>
	@endforeach
@endforeach
	<h3>New Goal</h3>
	<form action="{{ route('newGoal') }}" method="post">
	<div class="form-inline">
		<div class="form-group" id="new_goal">
			<label for="goalType" class="control-label">New goal:</label>
			<select class="form-control goalType" name="goalType" v-on:change="new_goal" v-model="selected">
			  <option value="wr">Weight x Rep</option>
			  <option value="rm">Estimate 1rm</option>
			  <option value="tv">Total volume</option>
			  <option value="tr">Total reps</option>
			</select>
			<input type="text" class="form-control" name="valueOne" value="">
			<span v-bind:class="{ 'hidden': hidden }"> x
			  <input type="text" class="form-control" name="valueTwo" value="">
			</span>
			<label for="newGoalExercise" class="control-label">Exercise:</label>
			<select class="form-control" name="exerciseId" id="newGoalExercise">
			@foreach ($exercises as $exercise)
		        <option value="{{ $exercise->exercise_id }}">{{ $exercise->exercise_name }}</option>
		    @endforeach
			</select>
	    </div>
	</div>
	{!! csrf_field() !!}
	<button type="submit" class="btn btn-default" name="action">Add</button>
	</form>
</div>
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/vue/1.0.17/vue.min.js" charset="utf-8"></script>
<script>
new Vue({
    el: '#new_goal',
    data: {
        hidden: false,
        selected: 'wr'
    },
    methods: {
        new_goal: function () {
            if (this.selected != 'wr')
            {
                this.hidden = true;
            }
            else
            {
                this.hidden = false;
            }
        }
    }
});
</script>
@endsection
