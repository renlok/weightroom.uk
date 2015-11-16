<!-- IF B_ERROR -->
<p class="alert bg-danger" role="alert">{ERROR}</p>
<!-- ENDIF -->
<h2>Rename: {EXERCISEOLD}</h2>
<form class="form-horizontal" action="?page=edit_exercise&exercise_name={EXERCISEOLD}" method="post">
  <div class="form-group">
    <label for="exercisenew" class="col-sm-2 control-label">Exercises <strong>new</strong> name:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="exercisenew" name="exercisenew" placeholder="New name" value="{EXERCISENEW}">
	  <small>If an exercise by the new name already exists the two will be merged</small>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
	  <input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
      <button type="submit" class="btn btn-default" name="action">Rename</button>
    </div>
  </div>
</form>