@extends('layouts.master')

@section('title', $exercise_name)

@section('headerstyle')
<link href="{{ asset(css/nv.d3.css) }}" rel="stylesheet">
<style>
#prHistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
</style>
@endsection

@section('content')
<h2>{{ $exercise_name }} <small><!-- IF TYPE eq 'weekly' -->Weekly maxes<!-- ELSEIF TYPE eq 'monthly' -->Monthly maxes<!-- ELSE -->PRs<!-- ENDIF --></small></h2>
<h3>Viewing: <!-- IF RANGE eq 0 -->All<!-- ELSE -->Last {RANGE} months<!-- ENDIF --></h3>
<small><a href="{{ route('listExercises') }}">&larr; Back to list</a></small> | <small><a href="{{ route('editExercise', ['exercise_name' => $exercise_name]) }}">Edit exercise</a></small> | <small><a href="{{ route('exerciseHistory', ['exercise_name' => $exercise_name]) }}">View history</a></small>

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
    <td>{{ (isset($filtered_prs[$i])) ? $filtered_prs[$i] : '-' }}</td>
@endfor
  </tr>
  <tr>
@for ($i = 1; $i <= 10; $i++)
@foreach ($current_prs as $pr)
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(1)}">{TRUE_PR_DATA(1)}</a><!-- ELSE -->{TRUE_PR_DATA(1)}<!-- ENDIF --></td>
@endfor
  </tr>
</tbody>
</table>

<div id="prHistoryChart">
    <svg></svg>
</div>

<!-- IF TYPE eq 'weekly' or TYPE eq 'monthly' -->
<p>Range:
<!-- IF RANGE ne 0 --><a href="?page=exercise&ex={EXERCISE}&do={TYPE}">All</a><!-- ELSE -->All<!-- ENDIF --> |
<!-- IF RANGE ne 12 --><a href="?page=exercise&ex={EXERCISE}&do={TYPE}&range=12">1 year</a><!-- ELSE -->1 year<!-- ENDIF --> |
<!-- IF RANGE ne 6 --><a href="?page=exercise&ex={EXERCISE}&do={TYPE}&range=6">6 months</a><!-- ELSE -->6 months<!-- ENDIF --> |
<!-- IF RANGE ne 3 --><a href="?page=exercise&ex={EXERCISE}&do={TYPE}&range=3">3 months</a><!-- ELSE -->3 months<!-- ENDIF --> |
<!-- IF RANGE ne 1 --><a href="?page=exercise&ex={EXERCISE}&do={TYPE}&range=1">1 month</a><!-- ELSE -->1 month<!-- ENDIF --></p>
<!-- IF TYPE eq 'weekly' -->
<p><a href="?page=exercise&ex={EXERCISE}&do=monthly">View monthly maxes</a> | <a href="?page=exercise&ex={EXERCISE}">View Prs</a></p>
<!-- ELSE -->
<p><a href="?page=exercise&ex={EXERCISE}&do=weekly">View weekly maxes</a> | <a href="?page=exercise&ex={EXERCISE}">View Prs</a></p>
<!-- ENDIF -->
<!-- ELSE -->
<p>Range:
<!-- IF RANGE ne 0 --><a href="?page=exercise&ex={EXERCISE}">All</a><!-- ELSE -->All<!-- ENDIF --> |
<!-- IF RANGE ne 12 --><a href="?page=exercise&ex={EXERCISE}&range=12">1 year</a><!-- ELSE -->1 year<!-- ENDIF --> |
<!-- IF RANGE ne 6 --><a href="?page=exercise&ex={EXERCISE}&range=6">6 months</a><!-- ELSE -->6 months<!-- ENDIF --> |
<!-- IF RANGE ne 3 --><a href="?page=exercise&ex={EXERCISE}&range=3">3 months</a><!-- ELSE -->3 months<!-- ENDIF --> |
<!-- IF RANGE ne 1 --><a href="?page=exercise&ex={EXERCISE}&range=1">1 month</a><!-- ELSE -->1 month<!-- ENDIF --></p>
<p><a href="?page=exercise&ex={EXERCISE}&do=weekly">View weekly maxes</a> | <a href="?page=exercise&ex={EXERCISE}&do=monthly">View monthly maxes</a></p>
<!-- ENDIF -->

<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<script src="{{ asset(js/nv.d3.js) }}"></script>

<script>
    function prHistoryData() {
		var prHistoryChartData = [];
		{GRAPH_DATA}
		return prHistoryChartData;
    }

    nv.addGraph(function() {
		var width = $(document).width() - 50;
		var height = Math.round(width/2);
        var chart = nv.models.lineChart()
						.margin({left: 100})  //Adjust chart margins to give the x-axis some breathing room.
						.useInteractiveGuideline(true)  //We want nice looking tooltips and a guideline!
						.duration(350)  //how fast do you want the lines to transition?
						.showLegend(true)       //Show the legend, allowing users to turn on/off line series.
						.showYAxis(true)        //Show the y-axis
						.showXAxis(true)        //Show the x-axis

        chart.xAxis
            .axisLabel('Date')
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        chart.yAxis
            .axisLabel('Weight')
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

@section('endjs')
@endsection
