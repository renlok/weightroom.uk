@extends('layouts.master')

@section('title', 'Register')

@section('content')
@include('errors.validation')
@include('common.flash')
<form class="form-horizontal" action="{{ route('register') }}" method="post">
  <div class="form-group">
    <label for="username" class="col-sm-2 control-label">Username</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Username" value="{{ old('user_name') }}">
    </div>
  </div>
  <div class="form-group">
    <label for="email" class="col-sm-2 control-label">Email</label>
    <div class="col-sm-10">
      <input type="email" class="form-control" id="user_email" name="user_email" placeholder="Email" value="{{ old('user_email') }}">
    </div>
  </div>
  <div class="form-group">
    <label for="password" class="col-sm-2 control-label">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" name="password" id="password" placeholder="Password">
    </div>
  </div>
  <div class="form-group">
    <label for="password" class="col-sm-2 control-label">Confirm Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Password">
    </div>
  </div>
  <div class="form-group">
    <label for="invcode" class="col-sm-2 control-label">Invite Code</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="invcode" name="invcode" placeholder="Invite Code" value="{{ old('invcode') }}">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
	  {{ csrf_field() }}
      <button type="submit" class="btn btn-default" name="action">Register</button>
    </div>
  </div>
</form>
@endsection
