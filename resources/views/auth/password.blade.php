<!-- resources/views/auth/password.blade.php -->
@extends('layouts.master')

@section('title', 'Forgot Password')

@section('content')
@include('errors.validation')
@include('common.flash')
<form class="form-horizontal" method="POST" action="{{ url('password/email') }}">
    {!! csrf_field() !!}
    <div class="form-group">
        <label>Email</label>
        <input class="form-control" type="email" name="email" value="{{ old('email') }}">
    </div>
    <div class="form-group">
        <button class="btn btn-default" type="submit">
            Send Password Reset Link
        </button>
    </div>
</form>
@endsection
