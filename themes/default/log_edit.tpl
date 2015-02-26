<!-- IF ERROR ne '' -->
	<p>{ERROR}</p>
<!-- ENDIF -->
<form action="?page=log&do=edit<!-- IF DATE ne '' -->&date={DATE}<!-- ENDIF -->" method="post">
enter log:<br>
<textarea rows="30" cols="70" name="log">
{LOG}
</textarea>
<br>
weight:<br>
<input type="text" name="weight" value="{WEIGHT}"> kg<br>
<input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
<input type="submit" name="action" value="add/edit log">
</form>