@extends('layouts.master')

@section('title', 'Register')

@section('content')
<div class="narrow-centre-block">
  @include('errors.validation')
  @include('common.flash')
  <form class="form-horizontal" action="{{ route('register') }}" method="post">
    <div class="form-group">
      <label for="username" class="control-label">Username</label>
      <input type="text" class="form-control input-lg" id="user_name" name="user_name" placeholder="Username" value="{{ old('user_name') }}">
    </div>
    <div class="form-group">
      <label for="email" class="control-label">Email</label>
      <input type="email" class="form-control input-lg" id="user_email" name="user_email" placeholder="Email" value="{{ $email }}">
    </div>
    <div class="form-group">
      <label for="password" class="control-label">Password</label>
      <input type="password" class="form-control input-lg" name="password" id="password" placeholder="Password">
    </div>
    <div class="form-group">
      <label for="password" class="control-label">Confirm Password</label>
      <input type="password" class="form-control input-lg" name="password_confirmation" id="password_confirmation" placeholder="Password">
    </div>
  @if (Admin::InvitesEnabled())
    <div class="form-group">
      <label for="invcode" class="control-label">Invite Code</label>
      <input type="text" class="form-control input-lg" id="invcode" name="invcode" placeholder="Invite Code" value="{{ $invcode }}">
    </div>
  @else
    <input type="hidden"name="invcode" value="{{ $invcode }}">
  @endif
    <div class="form-group">
  	  {{ csrf_field() }}
      <button type="submit" class="btn btn-default btn-lg" name="action">Register</button>
    </div>
  </form>
</div>
@endsection
