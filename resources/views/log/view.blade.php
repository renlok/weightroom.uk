@extends('layouts.master')

@section('title', 'View Log: ' . $date)

@section('headerstyle')
<link href="{{ asset('css/pickmeup.css') }}" rel="stylesheet">
<style>
h3.exercise {
  color:#337ab7;
}
.pmu-not-in-month.cal_log_date{
  background-color:#7F4C00;
}
.cal_log_date{
  background-color:#F90;
}
.log_comments, .comment_child {
  list-style: none;
}
.comment_child {
  padding-left:10px;
}
.comment_child li div {
  border-left: solid 1px #ddd;
  padding-left: 10px;
}
.comment h6 {
  margin-bottom: 0px;
}
.jcollapsible:hover, .jcollapsible:visited, .jcollapsible {
  text-decoration: none;
}
.calender-cont {
  text-align: center;
}
.user-info {
  width: 150px;
  padding-bottom: 10px;
}
.form-group, .comment-reply-box {
  border-left: none !important;
}
.leftspace {
  margin-left: 10px;
}
blockquote.small {
  font-size: 95%;
  padding: 5px 10px;
    margin: 10px;
}
.datebuttons {
    vertical-align: middle;
}
.empty-log {
  margin:40px 0 40px 0;
  font-size:50px;
}
</style>
@endsection

@section('content')
@include('common.flash')
@if (Session::has('new_prs'))
<div class="alert alert-info">
  @foreach (session('new_prs') as $exercise => $types)
    @foreach ($types as $type => $reps)
      @foreach ($reps as $rep => $weights)
        @foreach ($weights as $weight)
          @if ($type == 'W')
            <p>You have set a new <strong>{{ $exercise }} {{ $rep }}RM</strong> of <strong>{{ $weight }}</strong> kg</p>
          @elseif ($type == 'D')
            <p>You have set a new <strong>{{ $exercise }}</strong> personal best of <strong>{{ Format::format_distance($weight) }}</strong></p>
          @else
            <p>You have set a new <strong>{{ $exercise }}</strong> personal best of <strong>{{ Format::format_time($weight) }}</strong></p>
          @endif
        @endforeach
      @endforeach
    @endforeach
  @endforeach
</div>
@endif
@if (Session::has('goals_hit'))
<div class="alert alert-info alert-important alert-dismissible fade in">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  @foreach (session('goals_hit') as $goal)
    <p>You have hit your goal of
    @if ($goal['goal_type'] == 'wr')
      @if ($goal['exersice']->is_time)
        {{ Format::format_time($goal['goal_value_one'], true) }}
      @elseif ($goal['exersice']->is_distance)
        {{ Format::format_distance($goal['goal_value_one'], true) }}
      @else
        <strong>{{ Format::correct_weight($goal['goal_value_one']) }}</strong>{{ (Auth::check()) ? Auth::user()->user_unit : 'kg' }}
      @endif
      for {{$goal['goal_value_two']}}
    @elseif ($goal['goal_type'] == 'rm')
      getting an estimate 1rm of <strong>Format::correct_weight({{$goal['goal_value_one']}})</strong>{{ (Auth::check()) ? Auth::user()->user_unit : 'kg' }}
    @elseif ($goal['goal_type'] == 'tv')
      hitting a total volume of <strong>Format::correct_weight({{$goal['goal_value_one']}})</strong>{{ (Auth::check()) ? Auth::user()->user_unit : 'kg' }}
    @else
      hitting <strong>{{$goal['goal_value_one']}}</strong> total reps
    @endif
      ({{ $goal['exersice']->exercise_name }})
    </p>
  @endforeach
</div>
@endif
@if (Session::has('new_exercises'))
<div class="alert alert-info alert-important alert-dismissible fade in">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  @foreach (session('new_exercises') as $new_exercise)
    <p>You have logged {{ $new_exercise[0] }} for the first time</p>
    <p class="small">We have set it to be a {{ ($new_exercise[2]) ? 'endurance' : (($new_exercise[3]) ? 'distance' : (($new_exercise[1]) ? 'time' : 'weight')) }} exercise by default from now on, you change this in <a href="{{ route('editExercise', ['exercise_name' => $new_exercise[0]]) }}">edit exercise</a> page.</p>
  @endforeach
</div>
@endif
@if (Session::has('warnings'))
<div class="alert alert-danger alert-important alert-dismissible fade in">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  @foreach (session('warnings') as $warning => $bool)
    @if ($warning == 'blank_exercise')
      <p>One or more of the exercises you added did not have any workout data</p>
    @endif
    @if ($warning == 'blank_bodyweight')
      <p>You have entered a bodyweight exercise but have no entered your bodyweight</p>
    @endif
    @if ($warning == 'no_exercises')
      <p>You don't seem to have added any exercises to your log. Remember an exercise must be on its own line and start with a # like #squat or #run. If you're stuck click formatting help under the log entry box for help.</p>
    @endif
    @if ($warning == 'no_text')
      <p>You have entered a blank log, did you mean to do this or do you need some <a href="{{ route('faq') }}">help</a>?</p>
    @endif
  @endforeach
</div>
@endif
<div class="container-fluid">
  <div class="row">
    <div class="col-md-3 col-md-push-9">
      <div class="user-info">
        <h4>{{ $user->user_name }} @include('common.userBadges')</h4>
        <p><small>Member since: {{ $user->created_at->toDateString() }}</small></p>
@if (Auth::check() && $user->user_id != Auth::user()->user_id)
  @if ($is_following)
        <p class="btn btn-default"><a href="{{ route('unfollowUser', ['user' => $user->user_name, 'date' => $date]) }}">Unfollow <img src="{{ asset('img/user_delete.png') }}"></a></p>
  @else
        <p class="btn btn-default"><a href="{{ route('followUser', ['user' => $user->user_name, 'date' => $date]) }}">Follow <img src="{{ asset('img/user_add.png') }}"></a></p>
  @endif
@endif
      </div>
    </div>
    <div class="col-md-9 col-md-pull-3">
      <div class="calender-cont" style="max-width: 640px;">
        <div class="datebuttons text-left">
          <span class="h1">{!! $carbon_date->format('F j\<\s\u\p\>S\<\/\s\u\p\>, Y') !!}</h1>
          <span class="btn-group margintb" role="group" aria-label="Change Date">
            <a class="btn btn-default" role="button" href="{{ route('viewLog', ['date' => $carbon_date->subDay()->toDateString(),'user_name' => $user->user_name]) }}"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span></a>
            <a class="btn btn-default" role="button" href="{{ route('viewLog', ['date' => $carbon_date->addDay(2)->toDateString(),'user' => $user->user_name]) }}"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>
          </span>
  @if (Auth::check() && $user->user_id == Auth::user()->user_id)
    @if ($log != null)
          <a href="{{ route('editLog', ['date' => $date]) }}" class="btn btn-default">Edit Log</a>
          <button type="button" class="btn btn-danger deleteLink">Delete Log</button>
    @else
          <a href="{{ route('newLog', ['date' => $date]) }}" class="btn btn-default">Add Log</a>
    @endif
  @endif
        </div>
        <div class="date"></div>
      </div>
    </div>
  </div>
</div>

@if (Auth::check() && $user->user_id == Auth::user()->user_id && $log != null)
<div class="alert alert-danger margintb collapse" role="alert" id="deleteWarning" aria-expanded="false">
  <button type="button" class="close deleteLink"><span aria-hidden="true">&times;</span></button>
  <h4>You sure?</h4>
  <p>You are about to delete your workout entry for <strong>{{ $date }}</strong> this cannot be undone</p>
  <p>
    <a href="{{ route('deleteLog', ['date' => $date]) }}" class="btn btn-danger">Yeah delete it</a>
    <button type="button" class="btn btn-default deleteLink">Nah leave it be</button>
  </p>
</div>
@endif
@if ($log != null && $log_visible)
  <h2>Workout summary</h2>
  @if (($log->log_total_volume + ($log->log_failed_volume * $user->user_volumeincfails) - ($log->log_warmup_volume * $user->user_volumewarmup)) > 0)
    <p class="logrow">
      Volume: <span class="heavy">{{ Format::correct_weight($log->log_total_volume + ($log->log_failed_volume * $user->user_volumeincfails) - ($log->log_warmup_volume * $user->user_volumewarmup)) }}</span>{{ (Auth::check()) ? Auth::user()->user_unit : 'kg' }} - Reps: <span class="heavy">{{ $log->log_total_reps }}</span> - Sets: <span class="heavy">{{ $log->log_total_sets }}</span>
    @if (Auth::check() && Auth::user()->user_showintensity != 'h')
      - Avg. Intensity: <span class="heavy">{{ $log->average_intensity }}</span>
    @endif
    </p>
  @endif
  @if ($log->log_total_time > 0)
    <p class="logrow">
      Time: {!! Format::format_time($log->log_total_time, true) !!}</span>
    </p>
  @endif
  @if($log->log_total_distance > 0)
    <p class="logrow">
      Distance: {!! Format::format_distance($log->log_total_distance, true) !!}</span>
    </p>
  @endif
  @if ($log->log_weight > 0)
    <p class="logrow marginl"><small>Bodyweight: <span class="heavy">{{ Format::correct_weight($log->log_weight) }}</span>{{ (Auth::check()) ? Auth::user()->user_unit : 'kg' }}</small></p>
  @endif
  @if ($log->log_comment != '')
    <blockquote>
      {!! Format::replace_video_urls(nl2br(e($log->log_comment))) !!}
    </blockquote>
  @endif
  @foreach ($log->log_exercises as $log_exercise)
    @include('common.logExercise', ['view_type' => 'log'])
  @endforeach
  @include('common.commentTree', ['comments' => $comments])
@else
  <div class="row empty-log">
    <h1>Nothing's here</h1>
  </div>
@endif
@endsection

@section('endjs')
<script src="{{ asset('js/jquery.pickmeup.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js" charset="utf-8"></script>
<script src="{{ asset('js/jCollapsible.js') }}"></script>

<script>
var calendar_count = 3;
$(document).ready(function(){
    $('.log_comments').collapsible({
        xoffset:'-30',
        symbolhide:'[-]',
        symbolshow:'[+]'
    @if ($commenting)
        , defaulthide:false
    @endif
    });
    $('.deleteLink').click(function() {
        $('#deleteWarning').collapse('toggle');
    });
    $('.reply').click(function() {
        var element = $(this).parent().parent().find(".comment-reply-box").first();
        if ( element.is( ":hidden" ) ) {
            element.slideDown("slow");
        } else {
            element.slideUp("slow");
        }
        return false;
    });
    $('.delete').click(function() {
        var comment_id = $(this).attr('c-id');
        var element = $('#c' + comment_id).text('[Deleted]');
        $.ajax({
            url: "{{ route('deleteComment', ['comment_id' => ':cid']) }}".replace(':cid', comment_id),
            type: 'GET',
            dataType: 'json',
            cache: false
        });
        return false;
    });
    if ($( window ).width() < 500)
    {
        // that window is small
        $(".calender-cont").removeAttr('style');
        calendar_count = 1;
    }
    var arDates = {!! $calender['dates'] !!};
    var calMonths = {!! $calender['cals'] !!};

    $('.date').pickmeup({
        date   : moment('{{ $date }}','YYYY-MM-DD').format(),
        flat   : true,
        format : 'Y-m-d',
        change : function(e){
            var url = '{{ route("viewLog", ["date" => ":date", "user_name" => $user->user_name]) }}';
            window.location.href = url.replace(':date', e);
        },
        calendars : calendar_count,
        first_day : {{ empty($user->user_weekstart) ? 0 : $user->user_weekstart }},
        render: function(date) {
            var d = moment(date);
            var m = d.format('YYYY-MM');
            if ($.inArray(m, calMonths) == -1)
            {
                calMonths.push(m);
                loadlogdata(m);
            }
            if ($.inArray(d.format('YYYY-MM-DD'), arDates) != -1)
            {
                return {
                    class_name: 'cal_log_date'
                }
            }
        }
    });

    function loadlogdata(date)
    {
        var url = '{{ route("ajaxCal", ["date" => ":date", "user_name" => $user->user_name]) }}';
        $.ajax({
            url: url.replace(':date', date),
            type: 'GET',
            dataType: 'json',
            cache: true
        }).done(function(o) {
            $.merge(calMonths, o.cals);
            $.merge(arDates, o.dates);
            $('.date').pickmeup('update');
        }).fail(function() {}).always(function() {});
    }
});
</script>
@endsection
