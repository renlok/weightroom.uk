</div>

<!-- Modal -->
<div class="modal fade" id="searchUsers" tabindex="-1" role="dialog" aria-labelledby="searchUsersLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="searchUsersLabel">Search Users</h4>
      </div>
      <div class="modal-body">
		<form class="form-inline" method="post" action="?page=search">
		  <div class="form-group">
			<label class="sr-only">Username</label>
		  </div>
		  <div class="form-group">
			<label for="Username2" class="sr-only">Username</label>
			<input type="text" class="form-control" id="Username2" placeholder="Username" name="username">
		  </div>
		  <input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
		  <button type="submit" class="btn btn-default">Search</button>
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>