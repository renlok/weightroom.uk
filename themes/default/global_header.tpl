<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Weight Room - Track dem gains</title>
	<link rel="icon" type="image/x-icon" href="http://weightroom.uk/favicon.ico">
	<meta http-equiv="Content-Language" content="en"> 
	<meta name="keywords" content="training journal weight powerlifting strength power tracking weightlifting">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://we-link.co.uk/tracker/css/tracker.css">

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
		  <li role="presentation"><a href="?">Home</a></li>
	  <!-- IF NOT_LOGGED_IN -->
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'login' --> class="active"<!-- ENDIF -->><a href="?page=login">Login</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'register' --> class="active"<!-- ENDIF -->><a href="?page=register">Register</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'demo' --> class="active"<!-- ENDIF -->><a href="?page=demo">What is this?</a></li>
	  <!-- ELSE -->
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'log' --> class="active"<!-- ENDIF -->><a href="?page=log">View Log</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'exercise' && (CURRENT_DO eq 'list' || CURRENT_DO eq '') --> class="active"<!-- ENDIF -->><a href="?page=exercise&do=list">Exercise List</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'exercise' && CURRENT_DO eq 'compare' --> class="active"<!-- ENDIF -->><a href="?page=exercise&do=compare">Compare Exercises</a></li>
		  <li role="presentation"<!-- IF CURRENT_PAGE eq 'settings' --> class="active"<!-- ENDIF -->><a href="?page=settings"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></a></li>
	  <!-- ENDIF -->
		</ul>
	</div>
	<div class="container-fluid" id="body-div">
