@extends('layouts.master')

@section('title', 'View Log: ' . $date)

@section('headerstyle')
<style>
#editaddbutton {
	padding: 20px 0 0 0 ;
	margin: 0;
}
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
</style>
@endsection

@section('content')
@if (isset($new_prs) && count($new_prs) > 0)
<div class="alert alert-info">
	@foreach ($new_prs as $exercise => $types)
		@foreach ($types as $type => $reps)
			@foreach ($reps as $rep => $weights)
				@foreach ($weights as $weight)
					@if ($type == 'W')
						<p>You have set a new <strong>{{ $exercise }} {{ $rep }}RM</strong> of <strong>{{ $weight }}</strong> kg</p>
					@else
						<p>You have set a new <strong>{{ $exercise }} {{ $rep }}RM</strong> of <strong>{{ $weight }}</strong>s</p>
					@endif
				@endforeach
			@endforeach
		@endforeach
	@endforeach
</div>
@endif
@include('common.flash')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-3 col-md-push-9">
			<div class="user-info">
				<h4>{{ $user->user_name }} @include('common.userBadges')</h4>
				<p><small>Member since: {{ $user->created_at->toDateString() }}</small></p>
@if ($user->user_id != Auth::user()->user_id)
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
				<div class="btn-group margintb" role="group" aria-label="Change Date">
					<a class="btn btn-default" role="button" href="{{ route('viewLog', ['date' => $carbon_date->subDay()->toDateString(),'user_name' => $user->user_name]) }}"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>{{ $carbon_date->toDateString() }}</a>
					<button type="button" class="btn btn-default"><strong>{{ $carbon_date->addDay()->toDateString() }}</strong></button>
					<a class="btn btn-default" role="button" href="{{ route('viewLog', ['date' => $carbon_date->addDay()->toDateString(),'user' => $user->user_name]) }}">{{ $carbon_date->toDateString() }}<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>
				</div>
				<div class="date"></div>
			</div>
		</div>
	</div>
</div>

@if ($user->user_id == Auth::user()->user_id)
	@if ($log != null)
<p id="editaddbutton">
	<a href="{{ route('editLog', ['date' => $date]) }}" class="btn btn-default">Edit Log</a>
	<button type="button" class="btn btn-danger deleteLink pull-right">Delete Log</button>
</p>
<div class="alert alert-danger margintb collapse" role="alert" id="deleteWarning" aria-expanded="false">
	<button type="button" class="close deleteLink"><span aria-hidden="true">&times;</span></button>
	<h4>You sure?</h4>
	<p>You are about to delete your workout entry for <strong>{{ $date }}</strong> this cannot be undone</p>
	<p>
		<a href="{{ route('deleteLog', ['date' => $date]) }}" class="btn btn-danger">Yeah delete it</a>
		<button type="button" class="btn btn-default deleteLink">Nah leave it be</button>
	</p>
</div>
	@else
<p id="editaddbutton"><a href="{{ route('newLog', ['date' => $date]) }}" class="btn btn-default">Add Log</a></p>
	@endif
@endif
@if ($log != null)
	<h3>Workout summary</h3>
	<p class="logrow">
		Volume: <span class="heavy">{{ Format::correct_weight($log->log_total_volume + ($log->log_failed_volume * $user->user_volumeincfails)) }}</span>{{ Auth::user()->user_unit }} - Reps: <span class="heavy">{{ $log->log_total_reps }}</span> - Sets: <span class="heavy">{{ $log->log_total_reps }}</span>
	@if (Auth::user()->user_showintensity != 'h')
		- Avg. Intensity: <span class="heavy">{{ $log->average_intensity }}</span>
	@endif
	</p>
	<p class="logrow marginl"><small>Bodyweight: <span class="heavy">{{ Format::correct_weight($log->log_weight) }}</span>{{ Auth::user()->user_unit }}</small></p>
	@if ($log->log_comment != '')
	<div class="panel panel-default">
		<div class="panel-body">
			{!! Format::replace_video_urls(nl2br(e($log->log_comment))) !!}
		</div>
	</div>
	@endif
	@foreach ($log->log_exercises as $log_exercise)
		@include('common.logExercise', ['view_type' => 'log'])
	@endforeach
	@include('common.commentTree', ['comments' => $comments])
@endif
@endsection

@section('endjs')
<link href="{{ asset('css/pickmeup.css') }}" rel="stylesheet">
<script src="{{ asset('js/jquery.pickmeup.js') }}"></script>
<script src="//momentjs.com/downloads/moment.js"></script>
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
	var arDates = [];
	var calMonths = [];

	$('.date').pickmeup({
		date		: moment('{{ $date }}','YYYY-MM-DD').format(),
		flat		: true,
		format  	: 'Y-m-d',
		change		: function(e){
			var url = '{{ route("viewLog", ["date" => ":date", "user_name" => $user->user_name]) }}';
			window.location.href = url.replace(':date', e);
		},
		calendars	: calendar_count,
		first_day	: {{ empty($user->user_weekstart) ? 0 : $user->user_weekstart }},
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
			cache: false
		}).done(function(o) {
			$.merge(calMonths, o.cals);
			$.merge(arDates, o.dates);
			$('.date').pickmeup('update');
		}).fail(function() {}).always(function() {});
	}
});
</script>
@endsection
