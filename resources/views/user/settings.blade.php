@extends('layouts.master')

@section('title', 'Settings')

@section('content')
<h2>Edit user settings</h2>
@include('errors.simple')
@if ($settings_updated)
  <div class="alert bg-success" role="alert">
	Settings updated
  </div>
@endif
<form class="form-horizontal" action="{{ url('user/settings') }}" method="post">
  <div class="form-group">
    <div>
	<label for="gender">Gender</label>
	</div>
	<select class="form-control" id="gender" name="gender">
	  <option value="m" {{ $user->user_gender == 'm' ? 'selected' : '' }}>Male</option>
	  <option value="f" {{ $user->user_gender == 'f' ? 'selected' : '' }}>Female</option>
	</select>
  </div>
  <div class="form-group">
    <div>
	<label for="bodyweight">Current Bodyweight</label>
	</div>
	<input type="text" class="form-control" id="bodyweight" value="{{ $user->user_weight }}" name="bodyweight">
  </div>
  <div class="form-group">
    <div>
		<label for="weightunit">Default Unit</label>
		<p><small><i>What the site is displayed in also the default weight unit to use to use when no explicit weight unit is specified</i></small></p>
	</div>
	<label class="radio-inline">
	  <input type="radio" id="weightunit" name="weightunit" value="kg" {{ $user->user_unit == 'kg' ? 'checked' : '' }}> kg
	</label>
	<label class="radio-inline">
	  <input type="radio" id="weightunit" name="weightunit" value="lb" {{ $user->user_unit == 'lb' ? 'checked' : '' }}> lb
	</label>
  </div>
  <div class="form-group">
    <div>
  		<label for="weekstart">Start of the week</label>
  		<p><small><i>Which day do you want the calenders to show as the start of the week</i></small></p>
  	</div>
  	<label class="radio-inline">
  	  <input type="radio" id="weekstart" name="weekstart" value="1" {{ $user->user_weekstart == '1' ? 'checked' : '' }}> Monday
  	</label>
  	<label class="radio-inline">
  	  <input type="radio" id="weekstart" name="weekstart" value="0" {{ $user->user_weekstart == '0' ? 'checked' : '' }}> Sunday
  	</label>
  </div>
  <div class="form-group">
    <div>
  		<label for="showreps">Rep max. to show</label>
  		<p><small><i>These is the rep ranges which will show on the graphs in the exercise PR page</i></small></p>
  	</div>
@for ($i = 0; $i < 10; $i++)
    <label class="checkbox-inline">
      <input type="checkbox" name="showreps[]" id="inlineCheckbox{{ $i }}" value="{{ $i }}"{{ $showreps[$i] }}> {{ $i }} RM
    </label>
@endfor
  </div>
  <div class="form-group">
    <h3>For the Wilks Calculator</h3>
  </div>
  <div class="form-group">
    <label for="squat" class="control-label">Preferred squat variation for stats</label>
      <select name="squat" required id="squat" class="form-control">
			<option value="0">None selected</option>
@foreach ($exercises as $exercise)
			<option value="{{ $exercise->exercise_id }}"@if($exercise->exercise_id == $user->user_squatid) selected @endif>{{ $exercise->exercise_name }}</option>
@endforeach
	  </select>
  </div>
  <div class="form-group">
    <label for="bench" class="control-label">Preferred bench press variation for stats</label>
      <select name="bench" required id="bench" class="form-control">
			<option value="0">None selected</option>
@foreach ($exercises as $exercise)
			<option value="{{ $exercise->exercise_id }}"@if($exercise->exercise_id == $user->user_benchid) selected @endif>{{ $exercise->exercise_name }}</option>
@endforeach
	  </select>
  </div>
  <div class="form-group">
    <label for="deadlift" class="control-label">Preferred deadlift variation for stats</label>
      <select name="deadlift" required id="deadlift" class="form-control">
			<option value="0">None selected</option>
@foreach ($exercises as $exercise)
			<option value="{{ $exercise->exercise_id }}"@if($exercise->exercise_id == $user->user_deadliftid) selected @endif>{{ $exercise->exercise_name }}</option>
@endforeach
	  </select>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="control-label">&nbsp;</label>
    <h3>For the Sinclair Calculator</h3>
  </div>
  <div class="form-group">
    <label for="snatch" class="control-label">Preferred snatch variation for stats</label>
      <select name="snatch" required id="snatch" class="form-control">
			<option value="0">None selected</option>
@foreach ($exercises as $exercise)
			<option value="{{ $exercise->exercise_id }}"@if($exercise->exercise_id == $user->user_snatchid) selected @endif>{{ $exercise->exercise_name }}</option>
@endforeach
	  </select>
  </div>
  <div class="form-group">
    <label for="cnj" class="control-label">Preferred clean and jerk variation for stats</label>
      <select name="cnj" required id="cnj" class="form-control">
			<option value="0">None selected</option>
@foreach ($exercises as $exercise)
			<option value="{{ $exercise->exercise_id }}"@if($exercise->exercise_id == $user->user_cleanjerkid) selected @endif>{{ $exercise->exercise_name }}</option>
@endforeach
	  </select>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="control-label">&nbsp;</label>
    <h3>Advanced Settings</h3>
  </div>
  <div class="form-group">
    <div>
		<label for="volumeincfails">Include failed lifts in total tonnage (volume)</label>
		<p><small><i>If enabled when the total tonnage is calculatedfailed lifts will be included as a completed lift</i></small></p>
	</div>
	<label class="radio-inline">
	  <input type="radio" id="volumeincfails" name="volumeincfails" value="1" {{ $user->user_volumeincfails == '1' ? 'checked' : '' }}> enable
	</label>
	<label class="radio-inline">
	  <input type="radio" id="volumeincfails" name="volumeincfails" value="0" {{ $user->user_volumeincfails == '0' ? 'checked' : '' }}> disable
	</label>
  </div>
  <div class="form-group">
    <div>
		<label for="viewintensityabs">Show average intensity as % of current 1RM or as abolute value</label>
	</div>
	<label class="radio-inline">
	  <input type="radio" id="viewintensityabs" name="viewintensityabs" value="p" {{ $user->user_showintensity == 'p' ? 'checked' : '' }}> % of 1RM value
	</label>
	<label class="radio-inline">
	  <input type="radio" id="viewintensityabs" name="viewintensityabs" value="a" {{ $user->user_showintensity == 'a' ? 'checked' : '' }}> absolute value
	</label>
	<label class="radio-inline">
	  <input type="radio" id="viewintensityabs" name="viewintensityabs" value="h" {{ $user->user_showintensity == 'h' ? 'checked' : '' }}> hidden
	</label>
  </div>
  <div class="form-group">
    <div>
		<label for="limitintensity">Limit what is included in average intensity calculation</label>
		<p><small><i>If you track your warmups it is sometimes a good idea to ignore values lower than x% from the average intensity so they do not artificially bring the number down</i></small></p>
	</div>
	<div class="input-group">
	  <input type="text" class="form-control" id="limitintensity" name="limitintensity" value="{{ $user->user_limitintensity }}">
	  <div class="input-group-addon">%</div>
	</div>
  </div>
  <div class="form-group">
	  {!! csrf_field() !!}
      <button type="submit" class="btn btn-default" name="action">Save</button>
  </div>
</form>
@endsection
