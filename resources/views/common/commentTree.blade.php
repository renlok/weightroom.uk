<a name="comments"></a>
{LOG_COMMENTS}
<form action="?do=view&page=log&date={DATE}&user_id={USER_ID}#comments" method="post">
<input type="hidden" name="log_id" value="{LOG_ID}">
<input type="hidden" name="parent_id" value="0">
<input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
<div class="form-group">
	<textarea class="form-control" rows="3" placeholder="Comment" name="comment" maxlength="500"></textarea>
	<p><small>Max. 500 characters</small></p>
</div>
<div class="form-group">
	<button type="submit" class="btn btn-default">Post</button>
</div>
</form>
