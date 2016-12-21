@extends('layouts.master')

@section('title', 'Login')

@section('content')
<div class="narrow-centre-block">
  @include('errors.validation')
  @include('common.flash')
  <form class="form-horizontal" action="{{ route('login') }}" method="post">
    <div class="form-group">
      <label for="username" class="control-label">Username</label>
      <input type="text" class="form-control input-lg" id="username" name="username" placeholder="Username" value="{{ old('username') }}">
    </div>
    <div class="form-group">
      <label for="password" class="control-label">Password</label>
      <input type="password" class="form-control input-lg" id="password" name="password" placeholder="Password">
      <small><a href="{{ route('emailPassword') }}">Forgotten Password?</a></small>
    </div>
    <div class="form-group">
      <div class="checkbox">
        <label>
          <input type="checkbox" name="rememberme" value="1"> Remember me
        </label>
      </div>
    </div>
    <div class="form-group">
  	  {{ csrf_field() }}
      <button type="submit" class="btn btn-default btn-lg" name="action">Sign in</button>
    </div>
    <div class="form-group">
  	  Don't have an account yet? <a href="{{ route('register') }}">Create one now</a>
    </div>
  </form>
</div>
@endsection
