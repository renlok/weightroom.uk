@extends('layouts.master')

@section('title', 'Edit Exercise: ' . $exercise_name)

@section('content')
@include('errors.simple')
<h2>Rename: {{ $exercise_name }}</h2>
<form class="form-horizontal" action="{{ route('editExercise', ['exercise_name' => $exercise_name]) }}" method="post">
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
@endsection
