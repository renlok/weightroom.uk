<h2>User search</h2>
<form class="form-inline" method="post" action="?page=search">
  <div class="form-group">
	<label class="sr-only">Username</label>
  </div>
  <div class="form-group">
	<label for="Username2" class="sr-only">Username</label>
	<input type="text" class="form-control" id="Username2" placeholder="Username" name="username" value="{USER_NAME}">
  </div>
  <input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
  <button type="submit" class="btn btn-default">Search</button>
</form>

<h3>Results</h3>
<table class="table">
<tbody>
<!-- BEGIN user -->
	<tr>
		<td class="logrow">
			<a href="http://weightroom.uk/?page=log&user_id={user.USER_ID}">{user.USER_NAME}</a>
		</td>
	</tr>
<!-- BEGINELSE -->
	<tr>
		<td class="logrow">
			Your search returned no users
		</td>
	</tr>
<!-- END logs -->
</tbody>
</table>