<!-- IF B_ERROR -->
<p class="bg-danger header">...</p>
<!-- ENDIF -->
<h2>Rename the exercise</h2>
<form class="form-horizontal" action="?page=login" method="post">
  <div class="form-group">
    <label for="exercisenew" class="col-sm-2 control-label">Exercises <strong>new</strong> name:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="exercisenew" name="exercisenew" placeholder="New name" value="{EXERCISENEW}">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
	  <input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
      <button type="submit" class="btn btn-default" name="action">Rename</button>
    </div>
  </div>
</form>