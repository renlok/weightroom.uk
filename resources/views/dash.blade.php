@extends('layouts.master')

@section('title', 'Dashboard')

@section('headerstyle')
<style>
blockquote.small {
	font-size: 95%;
	padding: 5px 10px;
    margin: 10px;
    max-height: 300px;
    overflow: hidden;
}
blockquote.expanded {
	max-height: none;
}
#selected-btn {
	background-color: #f2f2f2;
}
#selected-btn:hover {
	background-color: #e6e6e6;
}
</style>
@endsection

@section('content')
<div class="padding-sb">
	<div class="row">
		<div class="pull-left">
			<h3>Recent Activity</h3>
			<div class="checkbox">
				<label id="expand-button"><input type="checkbox" id="expand-button-checkbox">Expand All</label>
			</div>
		</div>
		<div class="pull-right" style="margin-top: 20px;">
			<div class="btn-group btn-group-sm" role="group" aria-label="type">
				<a href="#" class="btn btn-default disabled" role="button">View:</a>
			  <a href="{{ route('dashboard') }}" class="btn btn-default" id="{{ ($button == 'follow') ? 'selected-btn' : '' }}">Following</a>
				<a href="{{ route('dashboardMe') }}" class="btn btn-default" id="{{ ($button == 'me') ? 'selected-btn' : '' }}">Just Me</a>
			  <a href="{{ route('dashboardAll') }}" class="btn btn-default" id="{{ ($button == 'all') ? 'selected-btn' : '' }}">All</a>
			</div>
		</div>
	</div>
</div>
@if ($random)
<h3 style="margin-top:0;">You aren't currently following anyone</h3>
<h4>We think a blank page is boring so here is a random selection of users you can check out</h4>
@else
	@if ($follow_count > 0)
<p class="small"><a href="{{ route('followingList') }}">Following {{ $follow_count }} users</a></p>
	@endif
@endif
<table class="table">
<tbody>
@forelse ($logs as $log)
	<tr>
		<td class="logrow">
			<a href="{{ route('viewUser', ['user_name' => $log->user->user_name]) }}">{{ $log->user->user_name }}</a> posted a log at <code>{{ $log->created_at->format('M j Y g:i a') }}</code> for {{ (Carbon::now()->startOfDay()->eq($log->log_date)) ? 'Today' : $log->log_date->diffForHumans() }}. <a href="{{ route('viewLog', ['date' => $log->log_date->toDateString(), 'user_name' => $log->user->user_name]) }}">View log</a>
      <blockquote class="small">{!! nl2br(htmlentities($log->log_text)) !!}</blockquote>
		</td>
	</tr>
@empty
  <tr>
    <td class="logrow">
      There has been no logs posted by people you follow
    </td>
  </tr>
@endforelse
</tbody>
</table>

{!! $logs->render() !!}
@endsection

@section('endjs')
	<script>
		$('#expand-button').click(function(){
		    var expandToggle = $('#expand-button-checkbox:checked').length > 0;
			$('blockquote.small').each(function(){
                if (expandToggle) {
					$(this).addClass('expanded');
				} else {
                    $(this).removeClass('expanded');
				}
			});
		});
	</script>
@endsection
