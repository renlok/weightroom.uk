@extends('layouts.master')

@section('title', 'Invite Codes')

@section('content')
<h2>Invite Codes</h2>
@if (!Admin::InvitesEnabled())
<div class="alert alert-danger" role="alert">
    Registration is currently open and invites are not needed to register
</div>
@endif
<p>Invite your lifting buddies to WeightRoom.uk and admire each others sweet lifts</p>
<table class="table">
  <tbody>
	<tr>
	  <th>Invite code</th>
	  <th>Invites remaining</th>
	  <th>Invite code expires</th>
	  <th>Link</th>
	</tr>
@forelse ($codes as $code)
	<tr>
	  <td>{{$code->code}}</td>
	  <td>{{$code->code_uses}}</td>
	  <td>{{$code->code_expires}}</td>
	  <td><a href="{{ route('register') }}?invcode={{ $code->code }}">Register Link</a></td>
	</tr>
@empty
	<tr>
	  <td colspan="4">You have no remaining invite codes</td>
	</tr>
@endforelse

  </tbody>
</table>
@endsection
