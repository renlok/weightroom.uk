@extends('layouts.master')

@section('title', 'Admin: List Templates')

@section('content')
<h2>List Templates</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

<p><a href="{{ route('adminAddTemplate') }}">Add Workout Template</a></p>
@include('common.flash')
@foreach ($templates as $tempalte)
<ul>
	<li><a href="{{ route('adminEditTemplate', ['template_id' => $tempalte->template_id]) }}">{{ $tempalte->template_name }}</a></li>
</ul>
@endforeach
@endsection
