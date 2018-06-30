@extends('layouts.master')

@section('title', 'List Users')

@section('content')
<h2>Admin Land: List Blog Posts</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Username</th>
      <th>Joined</th>
      <th>&nbsp;</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
@foreach ($users as $user)
  @if ($user->user_firstlog)
    <tr class="danger"> <!-- user has posted no logs -->
  @elseif ($user->subscribed('weightroom_gold'))
    <tr class="warning"> <!-- user has premium -->
  @else
    <tr>
  @endif
      <td>{{ $user->user_id }}</td>
      <td>{{ $user->user_name }}</td>
      <td>{{ $user->created_at->toDateString() }}</td>
      <td><a href="#">Edit</a></td>
      <td>
        <form action="{{ route('adminBanUser') }}" method="post">
          <input type="hidden" value="{{ $user->user_id }}" name="user_id">
          <input type="hidden" value="{{ intval(!$user->user_shadowban) }}" name="state">
          {!! csrf_field() !!}
          <button type="submit" class="btn btn-default">{{ $user->user_shadowban ? 'Unban' : 'Ban' }}</button>
        </form>
      </td>
    </tr>
@endforeach
  </tbody>
</table>

{!! $users->render() !!}
@endsection
