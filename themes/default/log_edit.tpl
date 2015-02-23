<!-- IF ERROR -->
	<p>logging faied</p>
<!-- ENDIF -->
<form action="?page=log&do=edit<!-- IF DATE ne '' -->&date={DATE}<!-- ENDIF -->" method="post">
enter log:<br>
<textarea rows="30" cols="70" name="log">
{LOG}
</textarea>
weight:
<input type="text" name="weight" value="{WEIGHT}"> kg
<input type="submit" name="action" value="add/edit log">
</form>