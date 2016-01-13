<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Weight Room - Track dem gains</title>
  	<!-- <base href="//weightroom.uk/"> -->
  	<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  	<meta http-equiv="Content-Language" content="en">
  	<meta name="description" content="The ultimate weightlifting and powerlifting workout tracker. Track each of your workouts with beautiful logging and analysis tools">
  	<meta name="keywords" content="workout tracker, workout journal, training journal, weight training, strength training, powerlifting, weightlifting, strongman">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/tracker.css') }}">
  	<script src="//code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
  	<script src="//getbootstrap.com/dist/js/bootstrap.min.js" charset="utf-8"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    @yield('headerstyle')
  </head>
  <body>
	<div class="container-fluid" id="header">
	  <ul class="nav nav-pills">
	  @if (!Auth::check())
		  <li role="presentation" class="{{ Request::is('login') ? 'active' : '' }}"><a href="{{ route('login') }}">Login</a></li>
		  <li role="presentation" class="{{ Request::is('register') ? 'active' : '' }}"><a href="{{ route('register') }}">Register</a></li>
		  <li role="presentation" class="{{ Request::is('demo') ? 'active' : '' }}"><a href="{{ route('demo') }}">What is this?</a></li>
	  @else
		  <li role="presentation" class="{{ Request::is('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}" class="hidden-xs">Home</a><a href="?" class="visible-xs"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
		  <li role="presentation" class="{{ (Request::is('log/*/new') || Request::is('log/*/edit')) ? 'active' : '' }}"><a href="{{ route('newLog', ['date' => Carbon\Carbon::now()->format('Y-m-d')]) }}">Track</a></li>
			<li class="dropdown visible-xs">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li role="presentation" class="{{ Request::is('log/*/view') ? 'active' : '' }}"><a href="{{ route('viewLog', ['date' => Carbon\Carbon::now()->format('Y-m-d')]) }}">View Log</a></li>
					<li role="presentation" class="{{ Request::is('exercise/*') ? 'active' : '' }}"><a href="{{ route('listExercises') }}">Exercise List</a></li>
					<li role="presentation" class="{{ Request::is('tools/*') ? 'active' : '' }}"><a href="{{ route('tools') }}">Tools</a></li>
				</ul>
			</li>
		  <li role="presentation" class="hidden-xs {{ Request::is('log/*/view') ? 'active' : '' }}"><a href="{{ route('viewLog', ['date' => Carbon\Carbon::now()->format('Y-m-d')]) }}">View Log</a></li>
		  <li role="presentation" class="hidden-xs {{ Request::is('exercise/*') ? 'active' : '' }}"><a href="{{ route('listExercises') }}">Exercise List</a></li>
		  <li role="presentation" class="hidden-xs {{ Request::is('tools/*') ? 'active' : '' }}"><a href="{{ route('tools') }}">Tools</a></li>
		  <li role="presentation" class="{{ Request::is('user/search') ? 'active' : '' }}"><a href="#" data-toggle="modal" data-target="#searchUsers"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></a></li>
		  <li class="dropdown {{ Request::is('user/settings') ? 'active' : '' }}">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li role="presentation"><a href="{{ route('invites') }}">Invite codes</a></li>
					<li role="presentation"><a href="//we-link.co.uk/projects/public/weightroom" target="_blank">Submit a bug</a></li>
					<li role="presentation"><a href="//weightroom.uk/blog/" target="_blank">Blog</a></li>
					<li role="presentation"><a href="{{ route('userSettings') }}">Settings</a></li>
					<li role="presentation"><a href="{{ route('logout') }}">Logout</a></li>
				</ul>
			</li>
            @if ($header_notifications_count > 0)
            <li class="dropdown" id="notification_bubble">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="badge">{{ $header_notifications_count }}</span></a>
                <ul class="dropdown-menu">
                @foreach ($header_notifications as $note)
                    @if ($note->notification_type == 'comment')
                        <li role="presentation"><a href="{{ route('viewLog', ['date' => $note->notification_from]) }}#comments">{{$note->notification_value}} commented on your log</a></li>
                    @endif
                    @if ($note->notification_type == 'reply')
                        <li role="presentation"><a href="{{ route('viewLog', ['date' => $note->notification_from]) }}#comments">{{$note->notification_value}} replied to your comment</a></li>
                    @endif
                    @if ($note->notification_type == 'follow')
                        <li role="presentation"><a href="{{ route('viewUser', ['user_name' => $note->notification_value]) }}">{{$note->notification_value}} started following you</a></li>
                    @endif
                @endforeach
                    <li role="presentation"><a href="#" id="clear_notes">Clear All</a></li>
                </ul>
            </li>
            @endif
	  @endif
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
		<form class="form-inline" method="post" action="{{ route('userSearch') }}">
		  <div class="form-group">
			<label class="sr-only">Username</label>
		  </div>
		  <div class="form-group">
			<label for="Username2" class="sr-only">Username</label>
			<input type="text" class="form-control" id="Username2" placeholder="Username" name="username">
		  </div>
          {{ csrf_field() }}
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
	<p class="text-muted">2015 &#169; weightroom.uk.<span class="hidden-xs"> Use of this site constitutes acceptance of the site's <a href="{{ route('privacyPolicy') }}">Privacy Policy and Terms of Use</a>.</span></p>
  </div>
</footer>

@yield('endjs')

<script>
@if (Session::has('flash_message'))
    $('div.alert').not('.alert-important').delay(3000).slideUp(300);
@endif
@if ($header_notifications_count > 0)
    $('#clear_notes').click(function(){
        $.ajax({
            url: "{{ route('clearNotifications') }}",
            cache: false
        });
    });
    $('#notification_bubble').hide();
@endif
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-1798088-8', 'auto');
  ga('send', 'pageview');

</script>

</body>
</html>
