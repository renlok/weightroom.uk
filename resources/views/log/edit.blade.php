@extends('layouts.master')

@section('title', ucfirst($type) . ' Log: ' . $date)

@section('headerstyle')
<link href="{{ asset('css/pickmeup.css') }}" rel="stylesheet">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.20.0/codemirror.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.20.0/addon/hint/show-hint.min.css">
@if ($user->user_firstlog)
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/intro.js/2.7.0/introjs.min.css">
@endif
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
#formattinghelp {
    display: none;
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
<h1 id="track_header">{{ ucfirst($type) }} log: {!! Carbon::createFromFormat('Y-m-d', $date)->format('F j\<\s\u\p\>S\<\/\s\u\p\>, Y') !!} <button class="btn btn-default glyphicon glyphicon-calendar" aria-hidden="true" id="track_date"></button></h1>
<small><a href="{{ route('viewLog', ['date' => $date]) }}">&larr; Back to log</a></small>

<form action="{{ url('log/' . $date . '/' . $type) }}" method="post">
<div class="form-group">
  <label for="log">Log Data:</label>
  <div id="log-box">
    <textarea rows="30" cols="50" name="log" id="log" class="form-control" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">{{ $log['log_text'] }}</textarea>
  </div>
  <a href="#formattinghelp" id="openhelp">Formatting help</a>
  <pre id="formattinghelp" class="cm-s-default"></pre>
  <textarea id='formattinghelptext' style="display:none;">
this is a comment about the entire workout

#squat
20 kg x 2 x 3 this is a comment, this set was 20kg for 2 reps for 3 sets
35 lb x 1 x 5 you can also use lb
45 x 2 x 5 or have no units in this case the units will be set by your accounts default setting
55 x 1 also works, meaning you did 1 set of 1 rep of 50
50 x 2,4,5 you can also use this format where you did 2 reps then 4 then 5

this is a comment about the squats in general

#pull up
BW x 1 x 5 some exersices dont use weight so you can use BW for bodyweight
BW+10x2x3 if you are doing a weighted bodyweight exercise you can add the weight you used
BW-10x2x3 same if you are doing a supported bodyweight exercise

you can have as many excersies as you want

#squat
40 x 5 @ 5 you can also note the RPE (Rating of Perceived Exertion) of the set
50 x 4 x 2 @9.5 the RPE is a scale of how hard the set was on a scale of 0-10
15kg, 15kg x 5, 5 x 5, 8 @ 6.0, 9.0 you can list multiple peices of information in the same row
BW-15kg, bw-15kg, BW+16kg

you can also have the same exercise multiple times

#swimming
15:15, 15:16 you can also track times
45 seconds, 56 hours, 44 mins in a bunch of ways
  </textarea>
</div>
<label for="weight">Bodyweight:</label>
<div class="input-group">
  <input type="text" class="form-control" placeholder="User's Weight" aria-describedby="bodyweight-addon" name="weight" value="{{ Format::correct_weight($log['log_weight']) }}">
  <span class="input-group-addon" id="bodyweight-addon">{{ $user->user_unit }}</span>
</div>
<div class="input-group margintb">
  {!! csrf_field() !!}
  <input type="submit" name="action" class="btn btn-default" id="log-submit" value="{{ ucfirst($type) }} log">
</div>
</form>
@endsection

@section('endjs')
<script src="{{ asset('js/jquery.pickmeup.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.3/moment.min.js" charset="utf-8"></script>
@if ($user->user_firstlog)
<script src="//cdnjs.cloudflare.com/ajax/libs/intro.js/2.7.0/intro.min.js"></script>
<script src="{{ asset('js/intro/tracker.js') }}"></script>
@endif
<script src="{{ mix('js/codemirror.js') }}"></script>
<script>
let arDates = {!! $calender['dates'] !!};
let calMonths = {!! $calender['cals'] !!};
const $ELIST = [{!! $exercises !!}];
const $GLIST = [{!! $exercise_groups !!}];
$('#track_date').pickmeup({
    date  : moment('{{ $date }}','YYYY-MM-DD').format(),
    format  : 'Y-m-d',
    change  : function(e){
        var url = '{{ route($type . "Log", ["date" => ":date", "user" => $user->user_name]) }}';
        window.location.href = url.replace(':date', e);
    },
    calendars : 1,
    first_day : {{ $user->user_weekstart }},
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
    var url = '{{ route("ajaxCal", ["date" => ":date", "user_name" => $user->user_name]) }}';
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
<script src="{{ mix('js/log.edit.js') }}"></script>
@endsection
