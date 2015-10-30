<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The ultimate weightlifting and powerlifting workout tracker | Weight Room - Track dem gains</title>
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
		  <li role="presentation"<!-- IF CURRENT_PAGE eq '' --> class="active"<!-- ENDIF -->><a href="?">Home</a></li>
	  <!-- IF NOT_LOGGED_IN -->
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'login' --> class="active"<!-- ENDIF -->><a href="?page=login">Login</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'register' --> class="active"<!-- ENDIF -->><a href="?page=register">Register</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'demo' --> class="active"<!-- ENDIF -->><a href="?page=demo">What is this?</a></li>
	  <!-- ELSE -->
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'log' && CURRENT_DO eq 'edit' --> class="active"<!-- ENDIF -->><a href="?do=edit&page=log">Track</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'log' && (CURRENT_DO eq 'view' || CURRENT_DO eq '') --> class="active"<!-- ENDIF -->><a href="?page=log">View Log</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'exercise' && (CURRENT_DO eq 'list' || CURRENT_DO eq '') --> class="active"<!-- ENDIF -->><a href="?page=exercise&do=list">Exercise List</a></li>
		  <li role="presentation"<!-- IF B_IN_TOOLS --> class="active"<!-- ENDIF -->><a href="?page=tools">Tools</a></li>
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
	  <!-- ENDIF -->
		</ul>
	</div>
	<div class="container-fluid" id="body-div">
