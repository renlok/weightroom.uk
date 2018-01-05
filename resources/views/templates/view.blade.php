@extends('layouts.master')

@section('title', 'View Template: ' . $template->template_name)

@section('headerstyle')
<style>
blockquote.small {
    margin: 0 !important;
    padding: 0 5px !important;
}
.template-log p {
    margin-left: 10px;
}
</style>
@endsection

@section('content')
<div class="row">
	<div class="col-md-6">
		<h2>{{ $template->template_name }}</h2>
	</div>
	<div class="col-md-6">
	@if ($is_active)
		<a href="{{ route('buildActiveTemplate') }}" class="btn btn-success h2">Generate Next Template</a>
	@else
		@if ($fixed_values)
		<a href="{{ route('setActiveTemplate', ['template_id' => $template->template_id]) }}" class="btn btn-default h2">Set As Current Template</a>
		@else
		<button data-toggle="modal" data-target="#activeTemplate" class="btn btn-default h2">Set As Current Template</button>
		@endif
	@endif
	</div>
</div>
@if ($template->template_charge > 0)
<p class="small">Purchased at: {{ $purchased_on }}</p>
@endif
<p class="small"><a href="{{ route('templatesHome') }}">← Back to templates</a></p>
@if ($template->template_description != '')
	<p>{{ $template->template_description }}</p>
@endif

@include('errors.validation')
@include('common.flash')

@foreach ($template->template_logs as $log)
<form method="post" action="{{ route('buildTemplate') }}">
	<input type="hidden" name="log_id" value="{{ $log->template_log_id }}">
	<input type="hidden" name="has_fixed_values" value="{{ $log->has_fixed_values }}">
	<div class="row">
		<div class="col-md-6">
			<h3>{{ $log->template_log_name }}</h3>
		</div>
		<div class="col-md-6 form-inline">
			<button type="submit" class="btn btn-default h3">Generate {{ $log->template_log_name }}</button>
		</div>
	</div>
	@if ($log->template_log_description != '')
		<p>{{ $log->template_log_description }}</p>
	@endif
	@if ($log->template_log_week != '')
		<p>Week: {{ $log->template_log_week }}, Day: {{ $log->template_log_day }}</p>
	@endif
	@foreach ($log->template_log_exercises as $log_exercises)
		<div class="template-log">
		@if ($log->has_fixed_values)
			<h4>{{ $log_exercises->texercise_name }}</h4>
		@else
			<div class="row">
				<div class="col-md-6"><h4>{{ $log_exercises->texercise_name }}</h4></div>
				<div class="col-md-6 form-inline">
					@include('common.exerciseDropdown', ['dropownName' => $log_exercises->logtempex_order, 'selected' => $log_exercises->texercise_name])
					Or 1RM:
					<input type="text" name="weight[{{ $log_exercises->logtempex_order }}]" class="form-control" placeholder="kg">
				</div>
			</div>
		@endif
		@if ($log_exercises->is_volume)
			<p>Total volume: {{ Format::format_weight($log_exercises->logtempex_volume) }}</p>
		@endif
		@if ($log_exercises->is_time)
			<p>Total Time: {{ Format::format_time($log_exercises->logtempex_time) }}</p>
		@endif
		@if ($log_exercises->is_distance)
			<p>Total volume: {{ Format::format_distance($log_exercises->logtempex_distance) }}</p>
		@endif
		@foreach ($log_exercises->template_log_items as $log_items)
			<p>
			@if ($log_items->is_bw)
				BW
			@elseif ($log_items->is_percent_1rm)
				{{ $log_items->percent_1rm }}%
			@elseif ($log_items->is_current_rm)
				{{ $log_items->current_rm }}RM
			@elseif ($log_items->is_weight)
				{{ Format::format_weight($log_items->logtempitem_weight) }}
			@elseif ($log_items->is_time)
				{{ Format::format_time($log_items->logtempitem_time) }}
			@elseif ($log_items->is_distance)
				{{ Format::format_distance($log_items->logtempitem_distance) }}
			@endif
			@if ($log_items->has_plus_weight)
				+ {{ Format::format_weight($log_items->logtempitem_plus_weight) }}
			@endif
			@if ($log_items->logtempitem_reps > 1 || $log_items->logtempitem_sets > 1)
				x {{ $log_items->logtempitem_reps }}
			@endif
			@if ($log_items->logtempitem_sets > 1)
				x {{ $log_items->logtempitem_sets }}
			@endif
			@if ($log_items->is_rpe)
				@{{ $log_items->logtempitem_rpe }}
			@endif
			</p>
			@if ($log_items->logtempitem_comment != '')
				<blockquote class="small">{{ $log_items->logtempitem_comment }}</blockquote>
			@endif
		@endforeach
		</div>
	@endforeach
	{!! csrf_field() !!}
</form>
@endforeach
@if($template->user_id == Auth::user()->user_id)
	<a href="{{ route('editTemplate', ['template_id' => $template->template_id]) }}" class="btn btn-primary h2">Edit</a>
@endif

@if (!($is_active && $fixed_values))
<div class="modal fade" id="activeTemplate" tabindex="-1" role="dialog" aria-labelledby="activeTemplateLabel">
	<div class="modal-dialog" role="document" style="width: 800px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="activeTemplateLabel">Set Active Template</h4>
			</div>
			<div class="modal-body">
				<form method="post" action="{{ route('setActiveTemplate', ['template_id' => $template->template_id]) }}">
					<div class="form-group">
						<p>Set <strong>{{ $template->template_name }}</strong> as your current template. Enter a 1RM or select an existing exercise to generate the templates from.</p>
					</div>
					@foreach ($template_exercises as $exercise)
					<div class="form-group">
						<div class="col-md-5"><h4>{{ $exercise }}</h4></div>
						<div class="col-md-7 form-inline">
							<input type="hidden" name="texercise_name[]" value="{{ $exercise }}">
							@include('common.exerciseDropdown', ['dropownName' => '', 'selected' => $exercise])
							Or 1RM:
							<input type="text" name="weight[]" class="form-control" placeholder="kg">
						</div>
					</div>
					@endforeach
					{{ csrf_field() }}
					<div class="form-group">
						<button type="submit" class="btn btn-default margintb">Confirm</button>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
@endif
@endsection
