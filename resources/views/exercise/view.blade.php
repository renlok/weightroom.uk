@extends('layouts.master')

@section('title', $exercise_name)

@section('headerstyle')
<link href="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.6/nv.d3.min.css" rel="stylesheet">
<style>
#prHistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
</style>
@endsection

@section('content')
<h2>{{ $exercise_name }} <small>
@if ($type == 'weekly')
    Weekly maxes
@elseif ($type == 'monthly')
    Monthly maxes
@elseif ($type == 'daily')
    Daily maxes
@else
    PRs
@endif
</small></h2>

@include('common.flash')

<div class="row">
    <div class="col-md-6">
        <h3>Viewing:
        @if ($range == 0)
            All
        @else
            Last {{ $range }} months
        @endif
        </h3>
    </div>
    <div class="col-md-6 text-right">
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Range <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => $type, 'range' => 0]) }}">All</a></li>
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => $type, 'range' => 12]) }}">1 year</a></li>
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => $type, 'range' => 6]) }}">6 months</a></li>
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => $type, 'range' => 3]) }}">3 months</a></li>
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => $type, 'range' => 1]) }}">1 month</a></li>
            </ul>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Graph Type <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => 'prs', 'range' => $range]) }}">PRs</a></li>
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => 'monthly', 'range' => $range]) }}">Monthly maxes</a></li>
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => 'weekly', 'range' => $range]) }}">Weekly maxes</a></li>
                <li><a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise_name_clean), 'type' => 'daily', 'range' => $range]) }}">Daily maxes</a></li>
            </ul>
        </div>
    </div>
</div>
<p><small><a href="{{ route('listExercises') }}">&larr; Back to list</a></small> | <small><a href="{{ route('editExercise', ['exercise_name' => rawurlencode($exercise_name_clean)]) }}">Edit exercise</a></small> | <small><a href="{{ route('exerciseHistory', ['exercise_name' => rawurlencode($exercise_name_clean)]) }}">View history</a></small></p>

<table width="100%" class="table">
<thead>
  <tr>
    <th>1RM</th>
    <th>2RM</th>
    <th>3RM</th>
    <th>4RM</th>
    <th>5RM</th>
    <th>6RM</th>
    <th>7RM</th>
    <th>8RM</th>
    <th>9RM</th>
    <th>10RM</th>
  </tr>
</thead>
<tbody>
  <tr>
@for ($i = 1; $i <= 10; $i++)
    <td>{{ (isset($filtered_prs[$i])) ? Format::$format_func($filtered_prs[$i][0]['pr_value'][0]) . ($filtered_prs[$i][0]['pr_value'][1] ? '*' : '') : '-' }}</td>
@endfor
  </tr>
  <tr>
@for ($i = 1; $i <= 10; $i++)
    <td>{!! (isset($current_prs[$i])) ? '<a href="' . route('viewLog', ['date' => Carbon::parse($current_prs[$i][0]['log_date'])->format('Y-m-d')]) . '">' . Format::$format_func($current_prs[$i][0]['pr_value']) . '</a>' : '-' !!}</td>
@endfor
  </tr>
</tbody>
</table>
<p class="text-right small"><a href="{{ route('viewExercisePRHistory', ['exercise_name' => rawurlencode($exercise_name_clean)]) }}">View PR history</a></p>

<div id="prHistoryChart">
    <svg></svg>
</div>

@if ($show_prilepin)
<h3>Prilepin's table</h3>
<table class="table">
    <tr>
        <th>Intensity</th>
        <th>Reps per set</th>
        <th>Optimal total</th>
        <th>Total range</th>
    </tr>
    <tr class="danger">
        <td>
            <b>{{ Format::$format_func($approx1rm * 0.9) }}</b> {{ Auth::user()->user_unit }} and up
            <small>(Over 90%)</small>
        </td>
        <td>1-2</td>
        <td>7</td>
        <td>4-10</td>
    </tr>
    <tr class="warning">
        <td>
            <b>{{ Format::$format_func($approx1rm * 0.8) }}</b> {{ Auth::user()->user_unit }} - <b>{{ Format::$format_func($approx1rm * 0.89) }}</b> {{ Auth::user()->user_unit }}
            <small>(80%-89%)</small>
        </td>
        <td>2-4</td>
        <td>15</td>
        <td>10-20</td>
    </tr>
    <tr class="success">
        <td>
            <b>{{ Format::$format_func($approx1rm * 0.7) }}</b> {{ Auth::user()->user_unit }} - <b>{{ Format::$format_func($approx1rm * 0.79) }}</b> {{ Auth::user()->user_unit }}
            <small>(70%-79%)</small>
        </td>
        <td>3-6</td>
        <td>18</td>
        <td>12-24</td>
    </tr>
    <tr class="info">
        <td>
            <b>{{ Format::$format_func($approx1rm * 0.7) }}</b> {{ Auth::user()->user_unit }} and less
            <small>(Less than 70%)</small>
        </td>
        <td>3-6</td>
        <td>24</td>
        <td>18-30</td>
    </tr>
</table>

<h3>Percent Max table</h3>
<table class="table">
    <tr>
        <th>Percent</th>
        <th>True Max</th>
        <th>Estimate Max</th>
    </tr>
@for ($i = 20; $i > 0; $i--)
    @if (!($i < 10 && $i % 2 == 1))
    <tr>
        <td>{{ $i*5 }}%</td>
        <td>{{ (isset($current_prs[1])) ? Format::$format_func($current_prs[1][0]['pr_value'] * (($i*5) / 100)) : '-' }}</td>
        <td>{{ Format::$format_func($approx1rm * (($i*5) / 100)) }}</td>
    </tr>
    @endif
@endfor
</table>
@endif
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.3/moment.min.js" charset="utf-8"></script>
<script src="{{ mix('js/graphing.js') }}" charset="utf-8"></script>

<script>
    function prHistoryData() {
		var prHistoryChartData = [];
@foreach ($prs as $rep_name => $graph_data)
		var dataset = [];
	@foreach ($graph_data as $data)
        @if ($type == 'monthly')
            dataset.push({x: moment('{{ $data->log_date->startOfMonth()->toDateString() }}','YYYY-MM-DD').toDate(), y: {{ $data->pr_value }}, shape:'circle'});
        @elseif ($type == 'weekly')
            dataset.push({x: moment('{{ $data->log_date->startOfWeek()->toDateString() }}','YYYY-MM-DD').toDate(), y: {{ $data->pr_value }}, shape:'circle'});
        @else
            dataset.push({x: moment('{{ $data->log_date->toDateString() }}','YYYY-MM-DD').toDate(), y: {{ $data->pr_value }}, shape:'circle'});
        @endif
	@endforeach
		prHistoryChartData.push({
			values: dataset,
			key: '{{ $rep_name }} rep max'
		});
@endforeach
		return prHistoryChartData;
    }

    nv.addGraph(function() {
		var width = $(document).width() - 50;
        if (width > 1150)
        {
            width = 1150;
        }
		var height = Math.round(width/2);
        var chart = nv.models.lineChart()
			.margin({left: 70})  //Adjust chart margins to give the x-axis some breathing room.
			.useInteractiveGuideline(true)  //We want nice looking tooltips and a guideline!
			.duration(350)  //how fast do you want the lines to transition?
			.showLegend(true)       //Show the legend, allowing users to turn on/off line series.
			.showYAxis(true)        //Show the y-axis
			.showXAxis(true)        //Show the x-axis

	chart.noData("Not enough data to generate PR graph");

    chart.xAxis
        .axisLabel('Date')
    @if ($type == 'monthly')
        .tickFormat(function(d) { return d3.time.format('%b %y')(new Date(d)); });
    @else
        .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });
    @endif

    chart.yAxis
        .axisLabel('{{ $graph_label }}')
        .tickFormat(d3.format('.02f'));

	d3.select('#prHistoryChart')
		.attr('style', "width: " + width + "px; height: " + height + "px;" );

        var data = prHistoryData();
        d3.select('#prHistoryChart svg')
		.datum(data)
		.transition().duration(500)
		.attr('perserveAspectRatio', 'xMinYMin meet')
		.call(chart);

        nv.utils.windowResize(resizeChart);
        function resizeChart() {
		var width = $(document).width() - 50;
		if (width > 1150)
		{
			width = 1150;
		}
		var height = Math.round(width/2);
		d3.select('#prHistoryChart')
			.attr('style', "width: " + width + "px; height: " + height + "px;" );
		chart.update();
        }

        return chart;
    });

    $(function()
    {
        $('#prHistoryChart .nv-lineChart circle.nv-point').attr("r", "3.5");
    });
</script>
@endsection
