<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | WeightRoom - Track dem gains</title>
    <base href="//weightroom.uk/">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <meta http-equiv="Content-Language" content="en">
    <meta name="description" content="@yield('description', 'The ultimate weightlifting and powerlifting workout tracker. Track each of your workouts with beautiful logging and analysis tools')">
    <meta name="keywords" content="@yield('keywords', 'workout tracker, workout journal, training journal, weight training, strength training, powerlifting, weightlifting, strongman')">

    <!-- PWS Data -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="apple-mobile-web-app-title" content="WeightRoom">
    <meta name="application-name" content="WeightRoom">
    <meta name="msapplication-TileColor" content="#00aba9">
    <meta name="theme-color" content="#ffffff">

    <!-- Bootstrap -->
    <link href='//fonts.googleapis.com/css?family=Open+Sans&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ mix('css/global.css') }}">
    <script src="//code.jquery.com/jquery-3.3.1.min.js" charset="utf-8"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" charset="utf-8"></script>

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
      <li role="presentation" class="{{ Request::is('login') ? 'active' : '' }}"><a href="{{ route('login') }}">{{ trans('master.login') }}</a></li>
      <li role="presentation" class="{{ Request::is('register') ? 'active' : '' }}"><a href="{{ route('register') }}">{{ trans('master.register') }}</a></li>
      <li role="presentation" class="{{ Request::is('tools*') ? 'active' : '' }}"><a href="{{ route('tools') }}">{{ trans('master.tools') }}</a></li>
      <li role="presentation" class="{{ Request::is('demo') ? 'active' : '' }}"><a href="{{ route('demo') }}">{{ trans('master.demo') }}</a></li>
    @else
      <li role="presentation" class="{{ Request::is('dashboard*') ? 'active' : '' }}"><a href="{{ route('dashboard') }}" class="hidden-xs">{{ trans('master.home') }}</a><a href="{{ route('dashboard') }}" class="visible-xs"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
      <li role="presentation" class="{{ (Request::is('log/*/new') || Request::is('log/*/edit') || Request::is('track')) ? 'active' : '' }}"><a href="{{ route('newLog', ['date' => Carbon\Carbon::now()->format('Y-m-d')]) }}">{{ trans('master.track') }}</a></li>
      <li class="dropdown visible-xs">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span> <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li role="presentation" class="{{ (Request::is('log/*') && !(Request::is('log/*/new') || Request::is('log/*/edit') || Request::is('log/volume*') || Request::is('log/search*'))) ? 'active' : '' }}"><a href="{{ route('viewLog', ['date' => Carbon\Carbon::now()->format('Y-m-d')]) }}">{{ trans('master.viewLog') }}</a></li>
          <li role="presentation" class="{{ Request::is('exercise/*') ? 'active' : '' }}"><a href="{{ route('listExercises') }}">{{ trans('master.exerciseList') }}</a></li>
          <li role="presentation" class="{{ (Request::is('tools*') || Request::is('log/volume*') || Request::is('log/search*')) ? 'active' : '' }}"><a href="{{ route('tools') }}">{{ trans('master.tools') }}</a></li>
        </ul>
      </li>
      <li role="presentation" class="hidden-xs {{ (Request::is('log/*') && !(Request::is('log/*/new') || Request::is('log/*/edit') || Request::is('log/volume*') || Request::is('log/search*'))) ? 'active' : '' }}"><a href="{{ route('viewLog', ['date' => Carbon\Carbon::now()->format('Y-m-d')]) }}">{{ trans('master.viewLog') }}</a></li>
      <li role="presentation" class="hidden-xs {{ Request::is('exercise/*') ? 'active' : '' }}"><a href="{{ route('listExercises') }}">{{ trans('master.exerciseList') }}</a></li>
      <li role="presentation" class="hidden-xs {{ (Request::is('tools*') || Request::is('log/volume*') || Request::is('log/search*')) ? 'active' : '' }}"><a href="{{ route('tools') }}">{{ trans('master.tools') }}</a></li>
      <li role="presentation" class="hidden-xs {{ (Request::is('templates*')) ? 'active' : '' }}"><a href="{{ route('templatesHome') }}">{{ trans('master.templates') }}</a></li>
      <li role="presentation" class="hidden-xs {{ Request::is('user/search') ? 'active' : '' }}"><a href="#" data-toggle="modal" data-target="#searchUsers"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></a></li>
      <li class="dropdown {{ (Request::is('user/settings') || Request::is('faq')) ? 'active' : '' }}">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span class="caret"></span></a>
        <ul class="dropdown-menu">
        @if (Admin::InvitesEnabled())
          <li role="presentation"><a href="{{ route('invites') }}">{{ trans('master.inviteCodes') }}</a></li>
        @endif
          <li role="presentation"><a href="{{ route('userPremium') }}" class="alert-warning strong"><span class="alert-warning strong"><span class="glyphicon glyphicon-star" aria-hidden="true"></span>&nbsp;{{ trans('master.premium') }}</span></a></li>
          <li role="presentation"><a href="{{ route('viewBlog') }}">{{ trans('master.blog') }}</a></li>
          <li role="presentation"><a href="{{ route('faq') }}">{{ trans('master.faq') }}</a></li>
          <li role="presentation"><a href="{{ route('userSettings') }}">{{ trans('master.settings') }}</a></li>
          <li role="presentation"><a href="{{ route('logout') }}">{{ trans('master.logout') }}</a></li>
        </ul>
      </li>
      @if (Auth::check() && Auth::user()->notifications->count() > 0)
      <li class="dropdown" id="notification_bubble">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="badge" id="notifications_count">{{ Auth::user()->notifications->count() }}</span></a>
        <ul class="dropdown-menu">
        @foreach (Auth::user()->notifications as $note)
          @if ($note->notification_type == 'comment')
            <li role="presentation"><a href="{{ route('viewLog', ['date' => $note->notification_from['log_date']]) }}#comments">{{ trans('notifications.logComment', ['username' => $note->notification_value]) }}</a><button type="button" class="close pull-right clear_note" aria-label="Close" note-id="{{ $note->notification_id }}"><span aria-hidden="true">×</span></button></li>
          @endif
          @if ($note->notification_type == 'reply')
            <li role="presentation"><a href="{{ route('viewLog', ['date' => $note->notification_from['log_date'], 'user_name' => $note->notification_from['user_name']]) }}#comments">{{ trans('notifications.commentReply', ['username' => $note->notification_value]) }}</a><button type="button" class="close pull-right clear_note" aria-label="Close" note-id="{{ $note->notification_id }}"><span aria-hidden="true">×</span></button></li>
          @endif
          @if ($note->notification_type == 'replyBlog')
            <li role="presentation"><a href="{{ route('viewBlogPost', ['url' => $note->notification_from['post_url']]) }}#comments">{{ trans('notifications.commentReplyBlog', ['username' => $note->notification_value]) }}</a><button type="button" class="close pull-right clear_note" aria-label="Close" note-id="{{ $note->notification_id }}"><span aria-hidden="true">×</span></button></li>
          @endif
          @if ($note->notification_type == 'follow')
            <li role="presentation"><a href="{{ route('followersList') }}">{{ trans('notifications.follower', ['username' => $note->notification_value]) }}</a><button type="button" class="close pull-right clear_note" aria-label="Close" note-id="{{ $note->notification_id }}"><span aria-hidden="true">×</span></button></li>
          @endif
          @if ($note->notification_type == 'mention')
              @if ($note->notification_from['location'] == 'log' || $note->notification_from['location'] == 'logcomments')
            <li role="presentation"><a href="{{ route('viewLog', ['date' => $note->notification_from['url_params']['log_date'], 'user_name' => $note->notification_from['url_params']['user_name']]) . ($note->notification_from['location'] == 'logcomments' ? '#comments' : '') }}">{{ trans('notifications.mention', ['username' => $note->notification_value]) }}</a><button type="button" class="close pull-right clear_note" aria-label="Close" note-id="{{ $note->notification_id }}"><span aria-hidden="true">×</span></button></li>
              @elseif ($note->notification_from['location'] == 'blog' || $note->notification_from['location'] == 'blogcomments')
            <li role="presentation"><a href="{{ route('viewBlogPost', ['url' => $note->notification_from['url_params']['url']]) . ($note->notification_from['location'] == 'blogcomments' ? '#comments' : '') }}">{{ trans('notifications.follower', ['username' => $note->notification_value]) }}</a><button type="button" class="close pull-right clear_note" aria-label="Close" note-id="{{ $note->notification_id }}"><span aria-hidden="true">×</span></button></li>
              @endif
          @endif
        @endforeach
          <li role="presentation"><a href="#" id="clear_notes">{{ trans('notifications.clearAll') }}</a></li>
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
  <div class="container text-muted">
    <ul class="list-inline pull-left footer-links">
      <li>2018 &#169; weightroom.uk</li>
      <li><a href="{{ route('privacyPolicy') }}">Privacy</a></li>
      <li><a href="{{ route('termsOfService') }}">Terms</a></li>
      <li><a href="{{ route('contactUs') }}">Contact</a></li>
      <li><a href="{{ route('faq') }}">Help</a></li>
    </ul>
    <ul class="list-inline pull-right hidden-xs footer-social">
      <li><a href="https://www.reddit.com/r/weightroomuk/"><img src="{{ asset('img/social/reddit.png') }}"></a></li>
      <li><a href="https://www.facebook.com/weightroom.uk"><img src="{{ asset('img/social/facebook.png') }}"></a></li>
      <li><a href="https://twitter.com/weightroom_uk"><img src="{{ asset('img/social/twitter.png') }}"></a></li>
    </ul>
  </div>
</footer>

@yield('endjs')

<script>
@if (Session::has('flash_message'))
    $('div.alert').not('.alert-important').delay(3000).slideUp(300);
@endif
@if (Auth::check() && Auth::user()->notifications->count() > 0)
    var notifications_count = {{ Auth::user()->notifications->count() }};
    $('#clear_notes').click(function(){
        $.ajax({
            url: "{{ route('clearNotifications') }}",
            cache: false
        });
        $('#notification_bubble').hide();
        return false;
    });
    $('.clear_note').click(function(){
        var note_id = $(this).attr('note-id');
        $.ajax({
            url: "{{ route('clearNotification', ['note_id' => ':nid']) }}".replace(':nid', note_id),
            cache: false
        });
        notifications_count--;
        $('#notifications_count').text(notifications_count);
        $(this).parent().hide();
        if (notifications_count < 1)
            $('#notification_bubble').hide();
        return false;
    });
@endif
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-1798088-8', 'auto');
ga('send', 'pageview');

Userback = window.Userback || {};
Userback.access_token = '879|996|JgEnNTf3tRs7hFTXUVBF7eMsd4vTMOmw41USU9Vo1nvi3ogXkR';

(function(id) {
    if (document.getElementById(id)) {return;}
    var s = document.createElement('script');
    s.id = id;
    s.src = 'https://static.userback.io/widget/v1.js';
    var parent_node = document.head || document.body;
    parent_node.appendChild(s);
})('userback-sdk');
</script>
</body>
</html>
