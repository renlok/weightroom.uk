@extends('layouts.master')

@section('title', 'Workout Templates: ' . ucwords($template_type))

@section('content')
    <h2>{{ ucwords($template_type) }} templates</h2>
    <p>{{ ucwords($template_type) }} templates for you to browse</p>
    <p><a href="{{ route('templatesHome') }}">Templates home</a></p>

    <ul>
        @foreach ($templates as $template)
        <li><a href="{{ route('viewTemplate', ['template_id' => $template->template_id]) }}">{{ $template->template_name }}</a></li>
        @endforeach
    </ul>

    {{ $templates->links() }}
@endsection
