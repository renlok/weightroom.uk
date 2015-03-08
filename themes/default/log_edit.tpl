<!-- IF ERROR ne '' -->
<p class="bg-danger">{ERROR}</p>
<!-- ENDIF -->
<h2>Log for {DATE}</h2>
<small><a href="?do=view&page=log&date={DATE}">&larr; Back to log</a></small>

<form action="?page=log&do=edit<!-- IF DATE ne '' -->&date={DATE}<!-- ENDIF -->" method="post">
<div class="form-group">
    <label for="log">Log Data:</label>
	<textarea rows="30" cols="50" name="log" id="log" class="form-control">{LOG}</textarea>
</div>
<label for="weight">Bodyweight:</label>
<div class="input-group">
	<input type="text" class="form-control" placeholder="User's Weight" aria-describedby="basic-addon2" name="weight" value="{WEIGHT}">
	<span class="input-group-addon" id="basic-addon2">kg</span>
</div>
<div class="input-group margintb">
	<input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
	<input type="submit" name="action" class="btn btn-default" value="<!-- IF VALID_LOG -->Edit<!-- ELSE -->Add<!-- ENDIF --> log">
</div>
</form>