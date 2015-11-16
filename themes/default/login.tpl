<!-- IF B_ERROR -->
<p class="alert bg-danger" role="alert">Username or password incorrect.</p>
<!-- ENDIF -->
<form class="form-horizontal" action="?page=login" method="post">
  <div class="form-group">
    <label for="username" class="col-sm-2 control-label">Username</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="{USERNAME}">
    </div>
  </div>
  <div class="form-group">
    <label for="password" class="col-sm-2 control-label">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="password" name="password" placeholder="Password">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <div class="checkbox">
        <label>
          <input type="checkbox" name="rememberme" value="1"> Remember me
        </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
	  <input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
      <button type="submit" class="btn btn-default" name="action">Sign in</button>
    </div>
  </div>
</form>