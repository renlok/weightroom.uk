<div class="container-fluid">
<h2>Edit user settings</h2>
<form class="form-horizontal" action="?page=settings" method="post">
<!-- IF ERROR ne '' -->
  <div class="form-group alert bg-danger" role="alert">
	{ERROR}
  </div>
<!-- ENDIF -->
<!-- IF SETTINGS_UPDATED -->
  <div class="form-group bg-success padding">
	Settings updated
  </div>
<!-- ENDIF -->
  <div class="form-group">
    <div>
	<label for="gender">Gender</label>
	</div>
	<select class="form-control" id="gender" name="gender">
	  <option value="1"<!-- IF GENDER eq 1 --> selected<!-- ENDIF -->>Male</option>
	  <option value="0"<!-- IF GENDER eq 0 --> selected<!-- ENDIF -->>Female</option>
	</select>
  </div>
  <div class="form-group">
    <div>
	<label for="bodyweight">Current Bodyweight</label>
	</div>
	<input type="text" class="form-control" id="bodyweight" value="{BODYWEIGHT}" name="bodyweight">
  </div>
  <div class="form-group">
    <div>
		<label for="weightunit">Default Unit</label>
		<p><small><i>What the site is displayed in also the default weight unit to use to use when no explicit weight unit is specified</i></small></p>
	</div>
	<label class="radio-inline">
	  <input type="radio" id="weightunit" name="weightunit" value="1"<!-- IF WEIGHTUNIT eq 1 --> checked<!-- ENDIF -->> kg
	</label>
	<label class="radio-inline">
	  <input type="radio" id="weightunit" name="weightunit" value="2"<!-- IF WEIGHTUNIT eq 2 --> checked<!-- ENDIF -->> lb
	</label>
  </div>
  <div class="form-group">
    <div>
		<label for="weekstart">Start of the week</label>
		<p><small><i>Which day do you want the calenders to show as the start of the week</i></small></p>
	</div>
	<label class="radio-inline">
	  <input type="radio" id="weekstart" name="weekstart" value="1"<!-- IF WEEKSTARTS eq 1 --> checked<!-- ENDIF -->> Monday
	</label>
	<label class="radio-inline">
	  <input type="radio" id="weekstart" name="weekstart" value="0"<!-- IF WEEKSTARTS eq 0 --> checked<!-- ENDIF -->> Sunday
	</label>
  </div>
  <div class="form-group">
    <div>
		<label for="showreps">Rep max. to show</label>
		<p><small><i>These is the rep ranges which will show on the graphs in the exercise PR page</i></small></p>
	</div>
    {SHOWREPHTML}
  </div>
  <div class="form-group">
    <h3>For the Wilks Calculator</h3>
  </div>
  <div class="form-group">
    <label for="squat" class="control-label">Preferred squat variation for stats</label>
      <select name="squat" required id="squat" class="form-control">
			<option value="0">None selected</option>
		<!-- BEGIN exercise -->
			<option value="{exercise.EXERCISE_ID}"<!-- IF exercise.EXERCISE_ID eq SQUATID --> selected<!-- ENDIF -->>{exercise.EXERCISE}</option>
		<!-- END -->
	  </select>
  </div>
  <div class="form-group">
    <label for="bench" class="control-label">Preferred bench press variation for stats</label>
      <select name="bench" required id="bench" class="form-control">
			<option value="0">None selected</option>
		<!-- BEGIN exercise -->
			<option value="{exercise.EXERCISE_ID}"<!-- IF exercise.EXERCISE_ID eq BENCHID --> selected<!-- ENDIF -->>{exercise.EXERCISE}</option>
		<!-- END -->
	  </select>
  </div>
  <div class="form-group">
    <label for="deadlift" class="control-label">Preferred deadlift variation for stats</label>
      <select name="deadlift" required id="deadlift" class="form-control">
			<option value="0">None selected</option>
		<!-- BEGIN exercise -->
			<option value="{exercise.EXERCISE_ID}"<!-- IF exercise.EXERCISE_ID eq DEADLIFTID --> selected<!-- ENDIF -->>{exercise.EXERCISE}</option>
		<!-- END -->
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
		<!-- BEGIN exercise -->
			<option value="{exercise.EXERCISE_ID}"<!-- IF exercise.EXERCISE_ID eq SNATCHID --> selected<!-- ENDIF -->>{exercise.EXERCISE}</option>
		<!-- END -->
	  </select>
  </div>
  <div class="form-group">
    <label for="cnj" class="control-label">Preferred clean and jerk variation for stats</label>
      <select name="cnj" required id="cnj" class="form-control">
			<option value="0">None selected</option>
		<!-- BEGIN exercise -->
			<option value="{exercise.EXERCISE_ID}"<!-- IF exercise.EXERCISE_ID eq CLEANJERKID --> selected<!-- ENDIF -->>{exercise.EXERCISE}</option>
		<!-- END -->
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
	  <input type="radio" id="volumeincfails" name="volumeincfails" value="1"<!-- IF VOLUMEINCFAILS eq 1 --> checked<!-- ENDIF -->> enable
	</label>
	<label class="radio-inline">
	  <input type="radio" id="volumeincfails" name="volumeincfails" value="0"<!-- IF VOLUMEINCFAILS eq 0 --> checked<!-- ENDIF -->> disable
	</label>
  </div>
  <div class="form-group">
    <div>
		<label for="viewintensityabs">Show average intensity as % of current 1RM or as abolute value</label>
	</div>
	<label class="radio-inline">
	  <input type="radio" id="viewintensityabs" name="viewintensityabs" value="0"<!-- IF ITENSITYABS eq 0 --> checked<!-- ENDIF -->> % of 1RM value
	</label>
	<label class="radio-inline">
	  <input type="radio" id="viewintensityabs" name="viewintensityabs" value="1"<!-- IF ITENSITYABS eq 1 --> checked<!-- ENDIF -->> absolute value
	</label>
	<label class="radio-inline">
	  <input type="radio" id="viewintensityabs" name="viewintensityabs" value="2"<!-- IF ITENSITYABS eq 2 --> checked<!-- ENDIF -->> hidden
	</label>
  </div>
  <div class="form-group">
    <div>
		<label for="limitintensity">Limit what is included in average intensity calculation</label>
		<p><small><i>If you track your warmups it is sometimes a good idea to ignore values lower than x% from the average intensity so they do not artificially bring the number down</i></small></p>
	</div>
	<div class="input-group">
	  <input type="text" class="form-control" id="limitintensity" name="limitintensity" value="{INTENSITY_LIMIT}">
	  <div class="input-group-addon">%</div>
	</div>
  </div>
  <div class="form-group">
	  <input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
      <button type="submit" class="btn btn-default" name="action">Save</button>
  </div>
</form>
</div>
