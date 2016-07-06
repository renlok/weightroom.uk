@extends('layouts.master')

@section('title', 'Generate Template: ' . $log->template_log_name)

@section('headerstyle')

@endsection

@section('content')
<h2>{{ $log->template_log_name }}</h2>
<p class="small"><a href="{{ route('viewTemplate', ['template_id' => $log->template_id]) }}">← Back to template</a></p>
@if ($log->template_log_description != '')
	<p>{{ $log->template_log_description }}</p>
@endif
@if ($log->template_log_week != '')
	<p>Week: {{ $log->template_log_week }}, Day: {{ $log->template_log_day }}</p>
@endif

<div>
@foreach ($log->template_log_exercises as $log_exercises)
	@if ($log->has_fixed_values)
		<p>#{{ $log_exercises->texercise_name }}</p>
	@else
		<p>#{{ $exercise_names[$log_exercises->logtempex_order] }}</p>
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
		{{ $exercise_values[$log_items->logtempitem_id] }}
		@if ($log_items->is_weight)
			kg
		@elseif ($log_items->is_time)
			s
		@elseif ($log_items->is_distance)
			m
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
			<p>{{ $log_items->logtempitem_comment }}</p>
		@endif
	@endforeach
@endforeach
</div>
@endsection
