@extends('templates.common.editTemplate')

@section('title', 'Admin: ' . (($template_id == 0) ? 'Add' : 'Edit') . ' Workout Template')

@section('content')
    <h2>Admin Land: {{ ($template_id == 0) ? 'Add' : 'Edit' }} Workout Template</h2>
    <p><a href="{{ route('adminHome') }}">Admin Home</a></p>

    @parent
@endsection

@php
    $submit_url = ($template_id == 0) ? route('adminAddTemplate') : route('adminEditTemplate', ['template_id' => $template_id]);
    $delete_url = route('adminDeleteTemplate', ['template_id' => $template_id]);
@endphp