@extends('layouts.master')

@section('title', 'Login')

@section('content')
@include('common.errors')
<form class="form-horizontal" action="{{ route('login') }}" method="post">
  <div class="form-group">
    <label for="username" class="col-sm-2 control-label">Username</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="{{ old('username') }}">
    </div>
  </div>
  <div class="form-group">
    <label for="password" class="col-sm-2 control-label">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="password" name="password" placeholder="Password">
      <small><a href="{{ route('password/email') }}">Forgotten Password?</a></small>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <div class="checkbox">
        <label>
          <input type="checkbox" name="rememberme" value="1"> Remember me
        </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
	  {{ csrf_field() }}
      <button type="submit" class="btn btn-default" name="action">Sign in</button>
    </div>
  </div>
</form>
@endsection
