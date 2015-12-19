<!-- resources/views/auth/password.blade.php -->
@extends('layouts.master')

@section('title', 'Forgot Password')

@section('content')
@include('errors.validation')
<form class="form-horizontal" method="POST" action="{{ route('password/email') }}">
    {!! csrf_field() !!}

    <div>
        Email
        <input class="form-control" type="email" name="email" value="{{ old('email') }}">
    </div>

    <div>
        <button class="btn btn-default" type="submit">
            Send Password Reset Link
        </button>
    </div>
</form>
@endsection
