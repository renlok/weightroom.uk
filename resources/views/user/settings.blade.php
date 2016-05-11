@extends('layouts.master')

@section('title', 'Settings')

@section('headerstyle')
<style>
.form-horizontal .form-group {
    margin-right: 15px;
    margin-left: 15px;
}
</style>
@endsection

@section('content')
<h2>Edit user settings</h2>
@include('errors.validation')
@include('common.flash')
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
	<input type="text" class="form-control" id="bodyweight" value="{{ round($user->user_weight, 2) }}" name="bodyweight">
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
@for ($i = 1; $i <= 10; $i++)
    <label class="checkbox-inline">
      <input type="checkbox" name="showreps[]" id="inlineCheckbox{{ $i }}" value="{{ $i }}"{{ in_array($i, $user->user_showreps) ? 'checked' : '' }}> {{ $i }} RM
    </label>
@endfor
    <p><small><i>If you want reps above 10 you can add them below separating each rep by a comma. e.g. 12,15,25</i></small></p>
    <input type="text" class="form-control" id="showextrareps" value="{{ implode(',', $user->user_showextrareps) }}" name="showextrareps">
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
      <button type="submit" class="btn btn-default" name="action">Save</button>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="control-label">&nbsp;</label>
    <h3>Advanced Settings <span id="showhide"><button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> Show</button></span></h3>
  </div>
  <div id="advanced_settings" style="display:none;">
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
    		<label for="volumeincwarmup">Include warmup lifts in total tonnage (volume)</label>
    	</div>
    	<label class="radio-inline">
    	  <input type="radio" id="volumeincwarmup" name="volumeincwarmup" value="1" {{ $user->user_volumeincwarmup == '1' ? 'checked' : '' }}> enable
    	</label>
    	<label class="radio-inline">
    	  <input type="radio" id="volumeincwarmup" name="volumeincwarmup" value="0" {{ $user->user_volumeincwarmup == '0' ? 'checked' : '' }}> disable
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
    		<label for="limitintensity">Remove warmup sets from average intensity calculation</label>
    	</div>
        <label class="radio-inline">
    	  <input type="radio" id="limitintensitywarmup" name="limitintensitywarmup" value="1" {{ $user->user_limitintensitywarmup == '1' ? 'checked' : '' }}> enable
    	</label>
    	<label class="radio-inline">
    	  <input type="radio" id="limitintensitywarmup" name="limitintensitywarmup" value="0" {{ $user->user_limitintensitywarmup == '0' ? 'checked' : '' }}> disable
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
        <div>
    		<label for="showinol">Show INoL value in logs</label>
    	</div>
        <label class="radio-inline">
    	  <input type="radio" id="showinol" name="showinol" value="1" {{ $user->user_showinol == '1' ? 'checked' : '' }}> enable
    	</label>
    	<label class="radio-inline">
    	  <input type="radio" id="showinol" name="showinol" value="0" {{ $user->user_showinol == '0' ? 'checked' : '' }}> disable
    	</label>
      </div>
      <div class="form-group">
        <div>
    		<label for="inolincwarmup">Include warmup sets from INoL calculation</label>
    	</div>
        <label class="radio-inline">
    	  <input type="radio" id="inolincwarmup" name="inolincwarmup" value="1" {{ $user->user_inolincwarmup == '1' ? 'checked' : '' }}> enable
    	</label>
    	<label class="radio-inline">
    	  <input type="radio" id="inolincwarmup" name="inolincwarmup" value="0" {{ $user->user_inolincwarmup == '0' ? 'checked' : '' }}> disable
    	</label>
      </div>
      <div class="form-group">
    	  {!! csrf_field() !!}
          <button type="submit" class="btn btn-default" name="action">Save</button>
      </div>
  </div>
</form>
@endsection

@section('endjs')
<script>
$('#showhide').click(function() {
    $('#advanced_settings').slideToggle('fast');
    $('#showhide').html(function(_,txt) {
        var ret='';

        if ( txt == '<button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> Show</button>' ) {
           ret = '<button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span> Hide</button>';
        } else {
           ret = '<button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> Show</button>';
        }
        return ret;
    });
    return false;
});
</script>
@endsection
