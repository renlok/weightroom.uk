@extends('layouts.master')

@section('title', 'List Exercises')

@section('content')
<h2>Admin Land: List Blog Posts</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Username</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
@forelse ($users as $user)
  @if ($user->user_firstlog)
    <tr class="danger"> <!-- user has posted no logs -->
  @elseif ($user->subscribed('weightroom_gold'))
    <tr class="warning"> <!-- user has premium -->
  @else
    <tr>
  @endif
      <td>{{ $user->user_id }}</td>
      <td>{{ $user->user_name }}</td>
      <td><a href="#">Edit</a></td>
    </tr>
@endforelse
  </tbody>
</table>

{!! $users->render() !!}
@endsection
