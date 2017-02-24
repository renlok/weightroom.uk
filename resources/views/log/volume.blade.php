@extends('layouts.master')

@section('title', 'Volume')

@section('headerstyle')
<link href="{{ asset('css/pickmeup.css') }}" rel="stylesheet">
<link href="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.5/nv.d3.min.css" rel="stylesheet">
<style>
.leftspace {
  margin-left: 10px;
}
#HistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
#HistoryChart, svg {
  height: 400px;
}
.nv-axisMaxMin, .nv-y text { display: none; }
.pmu-not-in-month.cal_log_date{
  background-color:#7F4C00;
}
.cal_log_date{
  background-color:#F90;
}
</style>
@endsection

@section('content')
<h2>Volume graph</h2>
<p>Shows how your total volume, reps and sets have varied over time.</p>
<p>The <a href="https://en.wikipedia.org/wiki/Moving_average#Simple_moving_average">moving average</a> option can be used to better see trends and remove noise.</p>

<h3>View a range of dates</h3>
@include('errors.validation')
<form class="form-inline" method="post" action="{{ url('log/volume') }}">
  <div class="form-group">
    <label for="from_date">From</label>
    <input type="text" class="form-control" id="from_date" name="from_date" value="{{ $from_date }}">
  </div>
  <div class="form-group">
    <label for="to_date">Until</label>
    <input type="text" class="form-control" id="to_date" name="to_date" value="{{ $to_date }}">
  </div>
  <div class="form-group">
    <label for="n">Moving Average</label>
  <select class="form-control" id="n" name="n">
    <option value="0" {{ $n == 0 ? 'selected' : '' }}>Disable</option>
    <option value="3" {{ $n == 3 ? 'selected' : '' }}>3</option>
    <option value="5" {{ $n == 5 ? 'selected' : '' }}>5</option>
    <option value="7" {{ $n == 7 ? 'selected' : '' }}>7</option>
  </select>
  </div>
  {{ csrf_field() }}
  <button type="submit" class="btn btn-default">Update</button>
</form>

<div id="HistoryChart">
    <svg></svg>
</div>
@endsection

@section('endjs')
<script src="{{ asset('js/jquery.pickmeup.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.17/d3.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.5/nv.d3.min.js" charset="utf-8"></script>
<script>
    $('#from_date').pickmeup({
        date           : moment('{{ $from_date }}','YYYY-MM-DD').format(),
        format         : 'Y-m-d',
        calendars      : 1,
        first_day      : {{ Auth::user()->user_weekstart }},
        hide_on_select : true
    });
    $('#to_date').pickmeup({
        date           : moment('{{ $to_date }}','YYYY-MM-DD').format(),
        format         : 'Y-m-d',
        calendars      : 1,
        first_day      : {{ Auth::user()->user_weekstart }},
        hide_on_select : true
    });

    function HistoryData() {
        var HistoryChartData = [];
@foreach ($graph_names as $table_name => $graph_name)
        var dataset{{ $table_name }} = [];
@endforeach
@foreach ($graph_data as $item)
    @foreach ($graph_names as $table_name => $graph_name)
        @if ((is_object($item)) ? ($item->$table_name > 0) : ($item[$table_name] > 0))
            @if ($table_name == 'log_total_volume')
        dataset{{ $table_name }}.push({x: moment('{{ is_object($item) ? $item->log_date : $item['log_date'] }}','YYYY-MM-DD').toDate(), y: {{ Format::correct_weight(is_object($item) ? $item->$table_name : $item[$table_name]) * $scales[$table_name] }}, shape:'circle'});
            @elseif ($table_name == 'log_total_distance')
        dataset{{ $table_name }}.push({x: moment('{{ is_object($item) ? $item->log_date : $item['log_date'] }}','YYYY-MM-DD').toDate(), y: {{ Format::correct_distance(is_object($item) ? $item->$table_name : $item[$table_name], 'm', 'km') * $scales[$table_name] }}, shape:'circle'});
            @elseif ($table_name == 'log_total_time')
        dataset{{ $table_name }}.push({x: moment('{{ is_object($item) ? $item->log_date : $item['log_date'] }}','YYYY-MM-DD').toDate(), y: {{ Format::correct_time(is_object($item) ? $item->$table_name : $item[$table_name], 's', 'h') * $scales[$table_name] }}, shape:'circle'});
            @else
        dataset{{ $table_name }}.push({x: moment('{{ is_object($item) ? $item->log_date : $item['log_date'] }}','YYYY-MM-DD').toDate(), y: {{ is_object($item) ? $item->$table_name : $item[$table_name] * $scales[$table_name] }}, shape:'circle'});
            @endif
        @endif
    @endforeach
@endforeach
@foreach ($graph_names as $table_name => $graph_name)
        HistoryChartData.push({
            values: dataset{{ $table_name }},
            key: '{{ $graph_name }}',
    @if ($table_name == 'log_total_reps')
            color: '#a6bf50'
    @elseif ($table_name == 'log_total_sets')
            color: '#56c5a6'
    @elseif ($table_name == 'log_total_volume')
            color: '#b84a68'
  @elseif ($table_name == 'log_total_distance')
            color: '#9F85C7'
  @else
      color: '#614DF2'
    @endif
        });
@endforeach
        return HistoryChartData;
    }

    nv.addGraph(function() {
        var chart = nv.models.lineWithFocusChart();
        chart.margin({left: -1});
        chart.tooltip.contentGenerator(function (obj)
        {
            var units = '{{ Auth::user()->user_unit }}';
            var point_value = obj.point.y;
            if (obj.point.color == '#b84a68')
                var tool_type = 'Volume';
    @if (isset($scales['log_total_reps']))
            if (obj.point.color == '#a6bf50')
            {
                var tool_type = 'Total reps';
                point_value = Math.round(point_value / {{ $scales['log_total_reps'] }});
                units = '';
            }
    @endif
    @if (isset($scales['log_total_sets']))
            if (obj.point.color == '#56c5a6')
            {
                var tool_type = 'Total sets';
                point_value = Math.round(point_value / {{ $scales['log_total_sets'] }});
                units = '';
            }
    @endif
    @if (isset($scales['log_total_time']))
            if (obj.point.color == '#614DF2')
            {
                var tool_type = 'Total time';
                point_value = Math.round(point_value / {{ $scales['log_total_time'] }});
                units = 'hr';
            }
    @endif
    @if (isset($scales['log_total_distance']))
            if (obj.point.color == '#9F85C7')
            {
                var tool_type = 'Total distance';
                point_value = Math.round(point_value / {{ $scales['log_total_distance'] }});
                units = 'km';
            }
    @endif
            return '<pre><strong>' + moment(obj.point.x).format('DD-MM-YYYY') + '</strong><br>' + tool_type + ': ' + point_value + units + '</pre>';
        });

        chart.xAxis
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        chart.x2Axis
            .axisLabel('Date')
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        chart.yAxis
            .tickFormat(d3.format('.02f'));

        chart.y2Axis
            .tickFormat(d3.format('.02f'));

        var data = HistoryData();
        d3.select('#HistoryChart svg')
            .datum(data)
            .transition().duration(500)
            .call(chart);

        nv.utils.windowResize(chart.update);

        return chart;
    });

    $(function()
    {
        $('#HistoryChart .nv-lineChart circle.nv-point').attr("r", "3.5");
    });
</script>
@endsection
