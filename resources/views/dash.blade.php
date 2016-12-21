@extends('layouts.master')

@section('title', 'Dashboard')

@section('headerstyle')
<style>
blockquote.small {
	font-size: 95%;
	padding: 5px 10px;
    margin: 10px;
    max-height: 300px;
    overflow: hidden;;
}
</style>
@endsection

@section('content')
<div class="padding-sb">
	<div class="row">
		<div class="pull-left">
			Recent Activity
		</div>
		<div class="pull-right" style="margin-top: 20px;">
			<div class="btn-group btn-group-sm" role="group" aria-label="type">
				<a href="#" class="btn btn-default disabled" role="button">View:</a>
			  <a href="{{ route('dashboard') }}" class="btn btn-default">Following</a>
			  <a href="{{ route('dashboardAll') }}" class="btn btn-default">All</a>
			</div>
		</div>
	</div>
</div>
@if ($random)
<h3>You aren't currently following anyone</h3>
<h4>We think a blank page is boring so here is a random selection of users you can check out</h4>
@endif
<table class="table">
<tbody>
@forelse ($logs as $log)
	<tr>
		<td class="logrow">
			<a href="{{ route('viewUser', ['user_name' => $log->user->user_name]) }}">{{ $log->user->user_name }}</a> posted a log {{ (Carbon::now()->startOfDay()->eq($log->log_date)) ? 'Today' : $log->log_date->diffForHumans() }}. <a href="{{ route('viewLog', ['date' => $log->log_date->toDateString(), 'user_name' => $log->user->user_name]) }}">View log</a>
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
