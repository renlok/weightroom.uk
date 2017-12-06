@extends('layouts.master')

@section('title', 'Edit Exercise: ' . $exercise_name)

@section('headerstyle')
<link href="{{ asset('css/tag-basic-style.css') }}" rel="stylesheet">
<style>
.hidden {
  display: none;
}
</style>
@endsection

@section('content')
<h2>Edit: {{ $exercise_name }}</h2>
@include('errors.validation')
<form class="form-horizontal" action="{{ route('editExerciseName', ['exercise_name' => rawurlencode($exercise_name_clean)]) }}" method="post">
  <div class="form-group">
    <h3>Rename exercise</h3>
  </div>
  <div class="form-group">
    <label for="exercisenew" class="col-sm-2 control-label">Exercises <strong>new</strong> name:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="exercisenew" name="exercisenew" placeholder="New name" value="{{ old('exercisenew') }}">
    <small>Caution: If an exercise by the new name already exists the two will be merged, this cannot be undone.</small>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
    {!! csrf_field() !!}
      <button type="submit" class="btn btn-default" name="action">Rename</button>
    </div>
  </div>
</form>

<h3>Exercise Groups</h3>
<div data-tags-input-name="tag" edit-on-delete="false" no-spacebar="true" id="exercise_groups">@foreach ($groups as $group){{ $group->exgroup_name }}, @endforeach</div>

<form class="form-horizontal" action="{{ route('editExercise', ['exercise_name' => rawurlencode($exercise_name_clean)]) }}" method="post">
  <div class="form-group">
    <h3>Change exercise type</h3>
  </div>
  <div class="form-group">
    <label for="exerciseType" class="col-sm-2 control-label">Change exercises default type:</label>
    <div class="col-sm-10">
        <select class="form-control" name="exerciseType" id="exerciseType">
            <option value="weight" {{ ($current_type == 'weight') ? 'selected="selected"' : '' }}>Weight</option>
            <option value="time" {{ ($current_type == 'time') ? 'selected="selected"' : '' }}>Time: for speed</option>
            <option value="enduracne" {{ ($current_type == 'enduracne') ? 'selected="selected"' : '' }}>Time: endurance</option>
            <option value="distance" {{ ($current_type == 'distance') ? 'selected="selected"' : '' }}>Distance</option>
        </select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
    {!! csrf_field() !!}
      <button type="submit" class="btn btn-default" name="action">Update</button>
    </div>
  </div>
</form>

<h3>Exercise Goals:</h3>
<form action="{{ route('updateExerciseGoals', ['exercise_name' => rawurlencode($exercise_name_clean)]) }}" method="post">
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
    </div>
  </div>
@if (count($goals) > 0)
  <h4>Edit goals</h4>
@endif
  <div id="old_goals">
    <div v-for="goal in goals">
      <div class="form-inline">
        <div class="form-group">
          <select class="form-control goalType" name="editGoalType[@{{ goal.goal_id }}]" v-on:change="old_goal(goal)" v-model="goal.goal_type">
            <option value="wr">Weight x Rep</option>
            <option value="rm">Estimate 1rm</option>
            <option value="tv">Total volume</option>
            <option value="tr">Total reps</option>
          </select>
          <input type="text" class="form-control" name="editValueOne[@{{ goal.goal_id }}]" v-model="goal.goal_value_one">
          <span v-bind:class="{ 'hidden': goal.hidden }"> x
            <input type="text" class="form-control" name="editValueTwo[@{{ goal.goal_id }}]" v-model="goal.goal_value_two">
          </span>
        </div>
      </div>
    <div class="padding">
      <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuenow="@{{ goal.percent }}" aria-valuemin="0" aria-valuemax="100" style="width: @{{ goal.percent }}%;">
          @{{ goal.percent }}%
        </div>
      </div>
    </div>
    </div>
  </div>
  {!! csrf_field() !!}
  <button type="submit" class="btn btn-default" name="action">Update</button>
</form>
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/vue/2.5.9/vue.min.js" charset="utf-8"></script>
<script src="{{ asset('js/tagging.min.js') }}"></script>
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
new Vue({
    el: '#old_goals',
    data: {
        goals: [
@foreach ($goals as $goal)
        {goal_id: {{ $goal->goal_id }}, goal_type: '{{ $goal->goal_type }}', goal_value_one: {{ $goal->goal_value_one }}, goal_value_two: {{ $goal->goal_value_two }}, hidden: {{ ( $goal->goal_type == 'wr' ) ? 'false' : 'true' }}, percent: {{ $goal->percentage }}},
@endforeach
        ]
    },
    methods: {
        old_goal: function (goal) {
            goal.hidden = (goal.goal_type != 'wr');
        }
    }
});
$(document).ready(function() {
    var t = $("#exercise_groups").tagging();
    $tag_box = t[0];
    // Execute callback when a tag is added
    $tag_box.on( "add:after", function ( el, text, tagging ) {
        $.ajax({
            url: '{{ route('addToExerciseGroup') }}',
            type: 'POST',
            data: {'group_name': text, 'exercise_name': '{{ $exercise_name }}', '_token': '{!! csrf_token() !!}'},
            dataType: 'json',
            cache: true
        }).done(function(o) {}).fail(function() {}).always(function() {});
    });

    // Execute callback when a tag is removed
    $tag_box.on( "remove:after", function ( el, text, tagging ) {
        $.ajax({
            url: '{{ route('deleteFromExerciseGroup') }}',
            type: 'POST',
            data: {'group_name': text, 'exercise_name': '{{ $exercise_name }}', '_token': '{!! csrf_token() !!}'},
            dataType: 'json',
            cache: true
        }).done(function(o) {}).fail(function() {}).always(function() {});
    });
});
</script>
@endsection
