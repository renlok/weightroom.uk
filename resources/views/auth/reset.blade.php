<!-- resources/views/auth/reset.blade.php -->
@extends('layouts.master')

@section('title', 'Reset password')

@section('content')
@include('errors.validation')
<form method="POST" action="{{ url('password/reset') }}">
    {!! csrf_field() !!}
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
        <label>Email</label>
        <input class="form-control" type="email" name="email" value="{{ old('email') }}">
    </div>

    <div class="form-group">
        <label>Password</label>
        <input class="form-control" type="password" name="password">
    </div>

    <div class="form-group">
        <label>Confirm Password</label>
        <input class="form-control" type="password" name="password_confirmation">
    </div>

    <div class="form-group">
        <button class="btn btn-default" type="submit">
            Reset Password
        </button>
    </div>
</form>
@endsection
