@extends('layouts.master')

@section('title', 'Admin: Settings')

@section('headerstyle')

@endsection

@section('content')
<h2>Admin Land: stats</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

@include('common.flash')
<form action="{{ route('adminSettings') }}" method="post">
	<div class="form-group">
	    <label for="invites_enabled">Invite Only</label>
		<div class="checkbox">
		<label class="radio-inline">
		  <input type="radio" name="invites_enabled" id="inviteOnlyT" value="1" {{ ($settings['invites_enabled']) ? 'checked="checked"' : '' }}> Enabled
		</label>
		<label class="radio-inline">
		  <input type="radio" name="invites_enabled" id="inviteOnlyF" value="0" {{ (!$settings['invites_enabled']) ? 'checked="checked"' : '' }}> Disabled
		</label>
	</div>
	</div>
	{!! csrf_field() !!}
	<button type="submit" class="btn btn-default">Submit</button>
</form>
@endsection

@section('endjs')

@endsection