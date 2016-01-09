@extends('layouts.master')

@section('title', 'Search Logs')

@section('content')
<h2>Search logs</h2>
<p>Find logs that meet the following criteria:</p>

<form class="form-horizontal" action="{{ url('log/search') }}" method="get">
  <div class="form-group">
    <label for="show" class="col-sm-2 control-label">Show</label>
    <div class="col-sm-10">
    <select class="form-control" name="show" id="show">
	  <option value="1"{{ (old('show') == 1) ? ' selected="selected"' : ''}}>the last log</option>
	  <option value="5"{{ (old('show') == 5) ? ' selected="selected"' : ''}}>the last five logs</option>
	  <option value="10"{{ (old('show') == 10) ? ' selected="selected"' : ''}}>the last ten logs</option>
	  <option value="0"{{ (old('show') == 0) ? ' selected="selected"' : ''}}>every log</option>
	</select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-10 col-md-offset-2">
      <p class="form-control-static">which meet these criteria</p>
    </div>
  </div>
  <div class="form-group">
    <label for="exercise" class="col-sm-2 control-label">Exercise</label>
    <div class="col-sm-10">
    <select class="form-control" name="exercise" id="exercise">
    @foreach ($exercises as $exercise)
        <option value="{{ $exercise->exercise_name }}"{{ ($exercise->exercise_name == old('exercises')) ? ' selected="selected"' : '' }}>{{ $exercise->exercise_name }}</option>
    @endforeach
	</select>
    </div>
  </div>
  <div class="form-group">
    <label for="weight" class="col-sm-2 control-label">Weight</label>
    <div class="col-sm-10">
    <div class="input-group">
      <select class="form-control" name="weightoperator" id="weightoperator">
        <option value="="{{ ('=' == old('weightoperator')) ? ' selected="selected"' : '' }}>=</option>
        <option value=">="{{ ('=' == old('weightoperator')) ? ' selected="selected"' : '' }}>&gt;=</option>
        <option value="<="{{ ('=' == old('weightoperator')) ? ' selected="selected"' : '' }}>&lt;=</option>
        <option value=">"{{ ('=' == old('weightoperator')) ? ' selected="selected"' : '' }}>&gt;</option>
        <option value="<"{{ ('=' == old('weightoperator')) ? ' selected="selected"' : '' }}>&lt;</option>
      </select>
      <input type="text" class="form-control" name="weight" id="weight" placeholder="Weight" value="{{ old('weight') }}">
	  <div class="input-group-addon">{{ Auth()::user()->user_unit }}</div>
	</div>
    </div>
  </div>
  <div class="form-group">
    <label for="reps" class="col-sm-2 control-label">Reps</label>
    <div class="col-sm-10">
    <input type="text" class="form-control" name="reps" id="reps" placeholder="any or a number" value="{{ old('reps') }}">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <input type="hidden" name="page" value="search_log">
      <button type="submit" class="btn btn-default">Search</button>
    </div>
  </div>
</form>

@foreach ($log_exercises as $log_exercise)
    @include('common.logExercise', ['view_type' => 'search'])
@endforeach
@endsection
