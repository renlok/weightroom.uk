<!-- resources/views/auth/reset.blade.php -->
@extends('layouts.master')

@section('title', 'Reset password')

@section('content')
@include('errors.validation')
<form method="POST" action="{{ route('password/reset') }}">
    {!! csrf_field() !!}
    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        Email
        <input class="form-control" type="email" name="email" value="{{ old('email') }}">
    </div>

    <div>
        Password
        <input class="form-control" type="password" name="password">
    </div>

    <div>
        Confirm Password
        <input class="form-control" type="password" name="password_confirmation">
    </div>

    <div>
        <button class="btn btn-default" type="submit">
            Reset Password
        </button>
    </div>
</form>
@endsection
