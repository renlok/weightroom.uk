@extends('templates.common.editTemplate')

@section('title', (($template_id == 0) ? 'Add' : 'Edit') . ' Workout Template')

@section('content')
    <h2>{{ ($template_id == 0) ? 'Add' : 'Edit' }} Workout Template</h2>
    <p><a href="{{ route('templatesHome') }}">Templates Home</a></p>

    @parent
@endsection

@php
$submit_url = ($template_id == 0) ? route('addTemplate') : route('editTemplate', ['template_id' => $template_id]);
$delete_url = route('deleteTemplate', ['template_id' => $template_id]);
@endphp