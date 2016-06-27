@extends('layouts.master')

@section('title', 'View Template: ' . $template->template_name)

@section('content')
<h2>{{ $template->template_name }}</h2>
<p class="small"><a href="{{ route('templatesHome') }}">← Back to templates</a></p>
@if ($template->template_description != '')
	<p>{{ $template->template_description }}</p>
@endif

@foreach ($template->template_logs as $log)
	<h3>{{ $log->template_log_name }}</h3>
	<p>{{ $template->template_log_description }}</p>
	<p>Week: {{ $template->template_log_week }}, Day: {{ $template->template_log_day }}</p>
	@foreach ($log->template_log_exercises as $log_exercises)
		<h4>{{ $log_exercises->texercise_name }}</h4>
		@foreach ($log_exercises->template_log_items as $log_items)

		@endforeach
	@endforeach
@endforeach
@endsection
