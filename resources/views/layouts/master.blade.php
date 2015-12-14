<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Weight Room - Track dem gains</title>
		<base href="http://weightroom.uk/">
		<link rel="icon" type="image/x-icon" href="http://weightroom.uk/favicon.ico">
		<meta http-equiv="Content-Language" content="en">
		<meta name="description" content="The ultimate weightlifting and powerlifting workout tracker. Track each of your workouts with beautiful logging and analysis tools">
		<meta name="keywords" content="workout tracker, workout journal, training journal, weight training, strength training, powerlifting, weightlifting, strongman">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://weightroom.uk/css/tracker.css">
		<script src="http://code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
		<script src="http://getbootstrap.com/dist/js/bootstrap.min.js" charset="utf-8"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
	<div class="container-fluid" id="header">
	  <ul class="nav nav-pills">
		  <li role="presentation"<!-- IF CURRENT_PAGE eq '' --> class="active"<!-- ENDIF -->><a href="?" class="hidden-xs">Home</a><a href="?" class="visible-xs"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
	  <!-- IF NOT_LOGGED_IN -->
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'login' --> class="active"<!-- ENDIF -->><a href="?page=login">Login</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'register' --> class="active"<!-- ENDIF -->><a href="?page=register">Register</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'demo' --> class="active"<!-- ENDIF -->><a href="?page=demo">What is this?</a></li>
	  <!-- ELSE -->
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'log' && CURRENT_DO eq 'edit' --> class="active"<!-- ENDIF -->><a href="?do=edit&page=log">Track</a></li>
			<li class="dropdown visible-xs">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li role="presentation"<!-- IF CURRENT_PAGE eq 'log' && (CURRENT_DO eq 'view' || CURRENT_DO eq '') --> class="active"<!-- ENDIF -->><a href="?page=log">View Log</a></li>
					<li role="presentation"<!-- IF CURRENT_PAGE eq 'exercise' && (CURRENT_DO eq 'list' || CURRENT_DO eq '') --> class="active"<!-- ENDIF -->><a href="?page=exercise&do=list">Exercise List</a></li>
					<li role="presentation"<!-- IF B_IN_TOOLS --> class="active"<!-- ENDIF -->><a href="?page=tools">Tools</a></li>
				</ul>
			</li>
		  <li role="presentation" class="hidden-xs<!-- IF CURRENT_PAGE eq 'log' && (CURRENT_DO eq 'view' || CURRENT_DO eq '') --> active<!-- ENDIF -->"><a href="?page=log">View Log</a></li>
		  <li role="presentation" class="hidden-xs<!-- IF CURRENT_PAGE eq 'exercise' && (CURRENT_DO eq 'list' || CURRENT_DO eq '') --> active<!-- ENDIF -->"><a href="?page=exercise&do=list">Exercise List</a></li>
		  <li role="presentation" class="hidden-xs<!-- IF B_IN_TOOLS --> active<!-- ENDIF -->"><a href="?page=tools">Tools</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'search' --> class="active"<!-- ENDIF -->><a href="#" data-toggle="modal" data-target="#searchUsers"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></a></li>
		  <li class="dropdown<!-- IF CURRENT_PAGE eq 'settings' --> active<!-- ENDIF -->">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li role="presentation"><a href="?page=invites">Invite codes</a></li>
					<li role="presentation"><a href="http://we-link.co.uk/projects/public/weightroom" target="_blank">Submit a bug</a></li>
					<li role="presentation"><a href="http://weightroom.uk/blog/" target="_blank">Blog</a></li>
					<li role="presentation"><a href="?page=settings">Settings</a></li>
					<li role="presentation"><a href="?page=logout">Logout</a></li>
				</ul>
			</li>
	  <!-- ENDIF -->
		</ul>
	</div>
	<div class="container-fluid" id="body-div">
    @yield('content')
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

<footer class="footer">
  <div class="container">
	<p class="text-muted">2015 &#169; weightroom.uk.<span class="hidden-xs"> Use of this site constitutes acceptance of the site's Privacy Policy and Terms of Use.</span></p>
  </div>
</footer>

@yield('endjs')

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-1798088-8', 'auto');
  ga('send', 'pageview');

</script>

</body>
</html>
