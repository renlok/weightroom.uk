@extends('layouts.master')

@section('title', 'Generate Template: ' . $template_name . ' - ' . $log->template_log_name)

@section('headerstyle')
<link href="{{ asset('css/pickmeup.css') }}" rel="stylesheet">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.22.0/codemirror.min.css">
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
.pickmeup {
  z-index:99999;
}
.pmu-not-in-month.cal_log_date {
  background-color:#7F4C00;
}
.cal_log_date {
  background-color:#F90;
}
</style>
@endsection

@section('content')
<div class="pull-left">
<h2>{{ $template_name }}: {{ $log->template_log_name }}</h2>
<p class="small"><a href="{{ route('viewTemplate', ['template_id' => $log->template_id]) }}">← Back to template</a></p>
@if ($log->template_log_description != '')
  <p>{{ $log->template_log_description }}</p>
@endif
@if ($log->template_log_week != '')
  <p>Week: {{ $log->template_log_week }}, Day: {{ $log->template_log_day }}</p>
@endif
</div>
<div class="pull-right hidden-print" style="margin-top: 40px;">
  <button type="button" class="btn btn-default btn-lg" id="track_date">
    <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
  </button>
  <button type="button" class="btn btn-default btn-lg print-button">
    <span class="glyphicon glyphicon-print" aria-hidden="true"></span>
  </button>
</div>
@include('errors.validation')

<pre id="template-data" class="cm-s-default" style="display: block; clear:both;">
{{ $log->template_log_name }}

@foreach ($log->template_log_exercises as $log_exercises)
{!! ($log->has_fixed_values || !isset($exercise_names[$log_exercises->logtempex_order])) ? '<span class="cm-ENAME">#' . $log_exercises->texercise_name . '</span>' : '<span class="cm-ENAME">#' . $exercise_names[$log_exercises->logtempex_order] . '</span>' !!}
@foreach ($log_exercises->template_log_items as $log_items)
<span class="cm-W">{{ ($log_items->is_distance) ? Format::format_distance($exercise_values[$log_items->logtempitem_id]) : ($log_items->is_time ? Format::format_time($exercise_values[$log_items->logtempitem_id]) : (($log_items->is_bw) ? $exercise_values[$log_items->logtempitem_id] : Format::format_weight($exercise_values[$log_items->logtempitem_id]))) }}</span>{!! ($log_items->logtempitem_reps > 1 || $log_items->logtempitem_sets > 1) ? '<span class="cm-R"> x ' . $log_items->logtempitem_reps . '</span>' : '' !!}{!! ($log_items->logtempitem_sets > 1) ? '<span class="cm-S"> x ' . $log_items->logtempitem_sets . '</span>' : '' !!}{!! ($log_items->is_rpe) ? '<span class="cm-RPE"> @' . $log_items->logtempitem_rpe . '</span>' : '' !!}{!! ($log_items->logtempitem_comment != '' && $log_items->is_warmup) ? '<span class="cm-C"> w|' . $log_items->logtempitem_comment . '</span>' : (($log_items->logtempitem_comment != '' && !$log_items->is_warmup) ? '<span class="cm-C"> ' . $log_items->logtempitem_comment . '</span>' : (($log_items->is_warmup) ? '<span class="cm-C"> w</span>' : '')) !!}
@endforeach
@if ($log_exercises->is_volume)
Total volume: {{ Format::format_weight($log_exercises->logtempex_volume) }}
@endif
@if ($log_exercises->is_time)
Total Time: {{ Format::format_time($log_exercises->logtempex_time) }}
@endif
@if ($log_exercises->is_distance)
Total volume: {{ Format::format_distance($log_exercises->logtempex_distance) }}
@endif

@endforeach
</pre>
<form class="hidden" action="{{ route("saveTemplate") }}" method="post" id="template-submit">
  <textarea id="template-text" name="template_text"></textarea>
  <input type="text" name="log_date" id="log-date" value="">
  {!! csrf_field() !!}
</form>
@endsection

@section('endjs')
<script src="{{ asset('js/jquery.pickmeup.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js" charset="utf-8"></script>
<script>
$('.print-button').click(function(){
     window.print();
});
var arDates = {!! $calender['dates'] !!};
var calMonths = {!! $calender['cals'] !!};

$('#track_date').pickmeup({
    date  : moment('{{ Carbon::now()->toDateString() }}','YYYY-MM-DD').format(),
    format  : 'Y-m-d',
    change  : function(e) {
        var url = '{{ route("saveTemplate") }}';
        $("#template-text").val($("#template-data").text());
        $("#log-date").val(e);
        $("#template-submit").submit();
    },
    calendars : 1,
    first_day : {{ Auth::user()->user_weekstart }},
    render: function(date) {
        var d = moment(date);
        var m = d.format('YYYY-MM');
        if ($.inArray(m, calMonths) == -1)
        {
            calMonths.push(m);
            loadlogdata(m);
        }
        if ($.inArray(d.format('YYYY-MM-DD'), arDates) != -1)
        {
            return {
                class_name: 'cal_log_date'
            }
        }
    }
});

function loadlogdata(date)
{
    var url = '{{ route("ajaxCal", ["date" => ":date", "user_name" => Auth::user()->user_name]) }}';
    $.ajax({
        url: url.replace(':date', date),
        type: 'GET',
        dataType: 'json',
        cache: true
    }).done(function(o) {
        $.merge(calMonths, o.cals);
        $.merge(arDates, o.dates);
        $('.date').pickmeup('update');
    }).fail(function() {}).always(function() {});
}
</script>
@endsection
