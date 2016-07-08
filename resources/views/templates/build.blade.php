@extends('layouts.master')

@section('title', 'Generate Template: ' . $template_name . ' - ' . $log->template_log_name)

@section('headerstyle')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/codemirror.min.css">
<style>
.cm-ENAME { color:#3338B7;}
.cm-W, .cm-WW { color:#337AB7;}
.cm-R, .cm-RR { color:#B7337A;}
.cm-S, .cm-SS { color:#7AB733;}
.cm-RPE, .cm-RPERPE { color: #D70;}
.cm-C { color:#191919; font-style: italic; }
.cm-error{ text-decoration: underline; background:#f00; color:#fff !important; }
.cm-YT { background: #4C8EFA; color:#fff !important;}
.CodeMirror {
	height: 500px;
	padding: 6px 12px;
	font-size: 14px;
	line-height: 1.42857143;
	color: #555;
	background-color: #fff;
	background-image: none;
	border: 1px solid #ccc;
	border-radius: 4px;
	-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
	box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
	-webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
	-o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
	transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
}
</style>
@endsection

@section('content')
<h2>{{ $template_name }}: {{ $log->template_log_name }}</h2>
<p class="small"><a href="{{ route('viewTemplate', ['template_id' => $log->template_id]) }}">← Back to template</a></p>
@if ($log->template_log_description != '')
	<p>{{ $log->template_log_description }}</p>
@endif
@if ($log->template_log_week != '')
	<p>Week: {{ $log->template_log_week }}, Day: {{ $log->template_log_day }}</p>
@endif

<pre id="formattinghelp" class="cm-s-default" style="display: block;">
@foreach ($log->template_log_exercises as $log_exercises)
{!! ($log->has_fixed_values || !isset($exercise_names[$log_exercises->logtempex_order])) ? '<span class="cm-ENAME">#' . $log_exercises->texercise_name . '</span>' : '<span class="cm-ENAME">#' . $exercise_names[$log_exercises->logtempex_order] . '</span>' !!}
@if ($log_exercises->is_volume)
Total volume: {{ $log_exercises->logtempex_volume }}
@endif
@if ($log_exercises->is_time)
Total Time: {{ $log_exercises->logtempex_time }}
@endif
@if ($log_exercises->is_distance)
Total volume: {{ $log_exercises->logtempex_distance }}
@endif
@foreach ($log_exercises->template_log_items as $log_items)
<span class="cm-W">{{ $exercise_values[$log_items->logtempitem_id] }}{{ ($log_items->is_distance) ? 'm' : ($log_items->is_time ? 's' : 'kg') }}</span>{!! ($log_items->logtempitem_reps > 1 || $log_items->logtempitem_sets > 1) ? '<span class="cm-R"> x ' . $log_items->logtempitem_reps . '</span>' : '' !!}{!! ($log_items->logtempitem_sets > 1) ? '<span class="cm-S"> x ' . $log_items->logtempitem_sets . '</span>' : '' !!}{!! ($log_items->is_pre) ? '<span class="cm-RPE"> @' . $log_items->logtempitem_pre . '</span>' : '' !!}{!! ($log_items->logtempitem_comment != '') ? '<span class="cm-C"> ' . $log_items->logtempitem_comment . '</span>' : '' !!}
@endforeach

@endforeach
</pre>
@endsection
