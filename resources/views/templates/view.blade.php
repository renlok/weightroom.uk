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
	@if ($template->template_description != '')
		<p>{{ $template->template_log_description }}</p>
	@endif
	@if ($template->template_log_week != '')
		<p>Week: {{ $template->template_log_week }}, Day: {{ $template->template_log_day }}</p>
	@endif
	@foreach ($log->template_log_exercises as $log_exercises)
		<h4>{{ $log_exercises->texercise_name }}</h4>
		@if ($log_exercises->logtempex_comment != '')
			<p>{{ $log_exercises->logtempex_comment }}</p>
		@endif
		@if ($log_exercises->is_volume)
			<p>Total volume: {{ $log_exercises->logtempex_volume }}</p>
		@endif
		@if ($log_exercises->is_time)
			<p>Total Time: {{ $log_exercises->logtempex_time }}</p>
		@endif
		@if ($log_exercises->is_distance)
			<p>Total volume: {{ $log_exercises->logtempex_distance }}</p>
		@endif
		@foreach ($log_exercises->template_log_items as $log_items)
			<p>
			@if ($log_items->is_percent_1rm)
				{{ $log_items->percent_1rm }}%
			@elseif ($log_items->is_current_rm)
				{{ $log_items->current_rm }}RM
			@elseif ($log_items->is_weight)
				{{ $log_items->logtempitem_weight }}kg
			@elseif ($log_items->is_time)
				{{ $log_items->logtempitem_time }}s
			@elseif ($log_items->is_distance)
				{{ $log_items->logtempitem_distance }}m
			@endif
			@if ($log_items->has_plus_weight)
				+ {{ $log_items->logtempitem_plus_weight }}kg
			@endif
			@if ($log_items->logtempitem_reps > 1 || $log_items->logtempitem_sets > 1)
				x {{ $log_items->logtempitem_reps }}
			@endif
			@if ($log_items->logtempitem_sets > 1)
				x {{ $log_items->logtempitem_sets }}
			@endif
			@if ($log_items->is_pre)
				@{{ $log_items->logtempitem_pre }}
			@endif
			</p>
			@if ($log_items->logtempitem_comment != '')
				<small>{{ $log_items->logtempitem_comment }}</small>
			@endif
		@endforeach
	@endforeach
@endforeach
@endsection
