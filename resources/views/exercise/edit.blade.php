@extends('layouts.master')

@section('title', 'Edit Exercise: ' . $exercise_name)

@section('content')
<h2>Edit: {{ $exercise_name }}</h2>
<form class="form-horizontal" action="{{ route('editExercise', ['exercise_name' => $exercise_name]) }}" method="post">
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

<form class="form-horizontal" action="{{ route('editExerciseType', ['exercise_name' => $exercise_name]) }}" method="post">
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
@endsection
