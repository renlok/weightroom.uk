@extends('layouts.master')

@section('title', 'Goals')

@section('headerstyle')
<script src="//cdnjs.cloudflare.com/ajax/libs/vue/2.2.6/vue.min.js" charset="utf-8"></script>
@endsection

@section('content')
<h2>Goals</h2>
@include('errors.validation')
@include('common.flash')

<div class="container">
@foreach($exercise_groups as $exercise_name => $exercise_goals)
  <h3>{{ ucwords($exercise_name) }}</h3>
  @foreach ($exercise_goals as $goal)
  <div id="goal-{{ $goal->goal_id }}">
    <div class="padding" v-show="goal_hidden">
    @if ($goal->goal_type == 'wr')
      <span><b>{{ $goal->goal_value_one }}</b> {{ Auth::user()->user_unit }} x <b>{{ $goal->goal_value_two }}</b></span>
    @elseif ($goal->goal_type == 'rm')
      <span>Estimate 1RM: <b>{{ $goal->goal_value_one }}</b> {{ Auth::user()->user_unit }}</span>
    @elseif ($goal->goal_type == 'tv')
      <span>Total volume: <b>{{ $goal->goal_value_one }}</b> {{ Auth::user()->user_unit }}</span>
    @else
      <span>Total Reps: <b>{{ $goal->goal_value_one }}</b></span>
    @endif
    <button class="btn btn-default btn-xs" v-on:click="edit_goal({{ $goal->goal_id }})">edit</button> | <button class="btn btn-default btn-xs" v-on:click="delete_goal({{ $goal->goal_id }})">delete</button>
      <div class="progress margintb">
        <div class="progress-bar" role="progressbar" aria-valuenow="{{ $goal->percentage }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $goal->percentage }}%;">
          {{ $goal->percentage }}%: {{ $goal->best }}
        </div>
      </div>
      <div class="edit" v-show="edit_hidden">
        <form action="{{ route('updateGoal') }}" method="post">
        <div class="form-inline">
          <div class="form-group" id="change_type">
            <select class="form-control goalType" name="goalType" v-on:change="change_type" v-model="selected">
              <option value="wr" {{ ($goal->goal_type == 'wr') ? 'selected' : '' }}>Weight x Rep</option>
              <option value="rm" {{ ($goal->goal_type == 'rm') ? 'selected' : '' }}>Estimate 1rm</option>
              <option value="tv" {{ ($goal->goal_type == 'tv') ? 'selected' : '' }}>Total volume</option>
              <option value="tr" {{ ($goal->goal_type == 'tr') ? 'selected' : '' }}>Total reps</option>
            </select>
            <input type="text" class="form-control" name="editValueOne" value="{{ $goal->goal_value_one }}">
            <span v-bind:class="{ 'hidden': hidden }"> x
              <input type="text" class="form-control" name="editValueTwo" value="{{ $goal->goal_value_two }}">
            </span>
            <input type="hidden" name="editGoalID" value="{{ $goal->goal_id }}">
            {!! csrf_field() !!}
            <button type="submit" class="btn btn-default" name="action">Edit</button>
          </div>
        </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    new Vue({
      el: '#goal-{{ $goal->goal_id }}',
      data: {
        goal_hidden: true,
        edit_hidden: false,
        hidden: {{ ($goal->goal_type != 'wr') ? 'true' : 'false' }},
            selected: '{{ $goal->goal_type }}'
      },
      methods: {
        change_type: function () {
            this.hidden = (this.selected != 'wr');
        },
        delete_goal: function(goal_id) {
          $.ajax({
            url: '{{ route('deleteGoal') }}',
            method: "POST",
            data: {
              id : goal_id,
              '_token': '{!! csrf_token() !!}'
            }
          }).done(function() {
            this.goal_hidden = false;
          });
          this.goal_hidden = false;
        },
        edit_goal: function() {
          this.edit_hidden = !this.edit_hidden;
        }
      }
    });
  </script>
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
<script>
new Vue({
    el: '#new_goal',
    data: {
        hidden: false,
        selected: 'wr'
    },
    methods: {
        new_goal: function () {
            this.hidden = (this.selected != 'wr');
        }
    }
});
</script>
@endsection
