@extends('layouts.master')

@section('title', 'Edit Exercise: ' . $exercise_name)

@section('content')
<h2>Edit: {{ $exercise_name }}</h2>
<form class="form-horizontal" action="{{ route('editExerciseName', ['exercise_name' => $exercise_name]) }}" method="post">
  <div class="form-group">
    <h3>Rename exercise</h3>
  </div>
  <div class="form-group">
    <label for="exercisenew" class="col-sm-2 control-label">Exercises <strong>new</strong> name:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="exercisenew" name="exercisenew" placeholder="New name" value="{{ old('exercisenew') }}">
	  <small>If an exercise by the new name already exists the two will be merged</small>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
	  {!! csrf_field() !!}
      <button type="submit" class="btn btn-default" name="action">Rename</button>
    </div>
  </div>
</form>

<form class="form-horizontal" action="{{ route('editExercise', ['exercise_name' => $exercise_name]) }}" method="post">
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

<form class="form-horizontal" action="{{ route('updateGoal', ['exercise_name' => $exercise_name]) }}" method="post">
  <div class="form-group">
    <h3>Exercise Goals:</h3>
  </div>
  <div class="form-group">
    <label for="exerciseType" class="col-sm-2 control-label">New goal</label>
    <div class="col-sm-10">
        <select class="form-control" name="goalType" id="goalType">
            <option value="wr">Weight x Rep</option>
            <option value="rm">Estimate 1rm</option>
            <option value="tv">Total volume</option>
            <option value="tr">Total reps</option>
          </select>
        <input type="text" name="valueOne" value="">
        <input type="text" name="valueTwo" value="">
    </div>
  </div>
@foreach ($goals as $goal)
<div class="form-group">
  <label for="exerciseType" class="col-sm-2 control-label">Edit:</label>
  <div class="col-sm-10">
        <select class="form-control" name="goalType" id="goalType">
            <option value="wr" {{ ($goal->goal_type == 'wr') ? 'selected="selected"' : '' }}>Weight x Rep</option>
            <option value="rm" {{ ($goal->goal_type == 'rm') ? 'selected="selected"' : '' }}>Estimate 1rm</option>
            <option value="tv" {{ ($goal->goal_type == 'tv') ? 'selected="selected"' : '' }}>Total volume</option>
            <option value="tr" {{ ($goal->goal_type == 'tr') ? 'selected="selected"' : '' }}>Total reps</option>
          </select>
        <input type="text" name="valueOne" value="{{ $goal->goal_value_one }}">
        <input type="text" name="valueTwo" value="{{ $goal->goal_value_two }}">
  </div>
  <div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
      60%
    </div>
  </div>
</div>
@endforeach
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
	  {!! csrf_field() !!}
      <button type="submit" class="btn btn-default" name="action">Update</button>
    </div>
  </div>
</form>
@endsection
