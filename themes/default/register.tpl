<!-- IF B_ERROR -->
<p class="bg-danger header">{ERROR}</p>
<!-- ENDIF -->
<form class="form-horizontal" action="?page=register" method="post">
  <div class="form-group">
    <label for="username" class="col-sm-2 control-label">Username</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="{USERNAME}">
    </div>
  </div>
  <div class="form-group">
    <label for="email" class="col-sm-2 control-label">Email</label>
    <div class="col-sm-10">
      <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{EMAIL}">
    </div>
  </div>
  <div class="form-group">
    <label for="password" class="col-sm-2 control-label">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" name="password" id="password" placeholder="Password">
    </div>
  </div>
  <div class="form-group">
    <label for="invcode" class="col-sm-2 control-label">Invite Code</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="invcode" name="invcode" placeholder="Invite Code">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
	  <input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
      <button type="submit" class="btn btn-default" name="action">Register</button>
    </div>
  </div>
</form>