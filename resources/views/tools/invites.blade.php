@extends('layouts.master')

@section('title', 'Weightroom Invite Codes')

@section('content')
<p>Invite your lifting buddies to weightroom.uk and admire each others sweet lifts</p>
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
	  <td><a href="{{ route('register') }}?invcode={{$code->code}}">Register Link</a></td>
	</tr>
@empty
	<tr>
	  <td colspan="3">You have no remaining invite codes</td>
	</tr>
@endforelse

  </tbody>
<!-- END invites -->
</table>
@endsection
