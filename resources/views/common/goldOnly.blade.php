@extends('layouts.master')

@section('title', 'Gold Members Only')

@section('headerstyle')

@endsection

@section('content')
<h1>Area for gold members only</h1>
<p class="lead">Please help support the site and get a <a href="{{ route('userPremium') }}">gold membership</a> to unlock this feature.</p>
@endsection

@section('endjs')

@endsection
