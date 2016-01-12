@extends('layouts.master')

@section('title', $exercise_name . ' History')

@section('headerstyle')
<link href="{{ asset('css/nv.d3.css') }}" rel="stylesheet">
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
</style>
@endsection

@section('content')
<h2>{{ $exercise_name }}</h2>
<small><a href="{{ route('listExercises') }}">&larr; Back to list</a></small> | <small><a href="{{ route('viewExercise', ['exercise_name' => $exercise_name]) }}">&larr; Back to exercise</a></small>

<div id="HistoryChart">
    <svg></svg>
</div>

<div class="panel-group margintb" id="workouthistory" role="tablist" aria-multiselectable="true">
@foreach ($log_exercises as $log_exercise)
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading{{ $log_exercise->log_date->toDateString() }}">
      <h4 class="panel-title">
        <a class="collapsed" data-toggle="collapse" data-parent="#workouthistory" href="#collapse{{ $log_exercise->log_date->toDateString() }}" aria-expanded="false" aria-controls="collapse{{ $log_exercise->log_date->toDateString() }}">
          {{ $log_exercise->log_date->toDateString() }}
        </a>
      </h4>
    </div>
    <div id="collapse{{ $log_exercise->log_date->toDateString() }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{{ $log_exercise->log_date->toDateString() }}">
      <div class="panel-body">
        @include('common.logExercise', ['view_type' => 'history'])
		<small><a href="{{ route('viewLog', ['date' => $log_exercise->log_date->toDateString()]) }}">View log</a></small>
      </div>
    </div>
  </div>
@endforeach
</div>
@endsection

@section('endjs')
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<script src="{{ asset('js/nv.d3.js') }}"></script>
<script>
    function HistoryData() {
		var HistoryChartData = [];
@foreach ($graph_names as $table_name => $graph_name)
		var dataset{{ $table_name }} = [];
@endforeach
@foreach ($graph_data as $item)
		@foreach ($graph_names as $table_name => $graph_name)
			dataset{{ $table_name }}.push({x: moment('{{ $item->log_date }}','YYYY-MM-DD').toDate(), y: {{ $item->$table_name * $scales[$table_name] }}, shape:'circle'});
		@endforeach
@endforeach
@foreach ($graph_names as $table_name => $graph_name)
		HistoryChartData.push({
			values: dataset{{ $table_name }},
			key: '{{ $graph_name }}'
		});
@endforeach
		return HistoryChartData;
    }

    nv.addGraph(function() {
        var chart = nv.models.lineWithFocusChart();
		chart.margin({left: -50});
		chart.tooltipContent(function(key, y, e, graph)
		{
			//console.log(key);
			var units = '{{ $user->user_unit }}';
			var point_value = key.point.y;
			if (key.point.color == '#b84a68')
				var tool_type = 'Volume';
			if (key.point.color == '#a6bf50')
			{
				var tool_type = 'Total reps';
				point_value = Math.round(point_value / {{ $scales['Total reps'] }});
				units = '';
			}
			if (key.point.color == '#56c5a6')
			{
				var tool_type = 'Total sets';
				point_value = Math.round(point_value / {{ $scales['Total sets'] }});
				units = '';
			}
			if (key.point.color == '#765dcb')
			{
				var tool_type = '1RM';
				point_value = (point_value / {{ $scales['1RM'] }}).toFixed(2);
			}
			if (key.point.color == '#907fcc')
			{
				var tool_type = 'Average Intensity';
				point_value = Math.round(point_value / {{ $scales['ai'] }});
				units = '%';
			}
			return '<pre>' + tool_type + ': ' + point_value + units + '</pre>';
		})
							//.margin({left: 100})  //Adjust chart margins to give the x-axis some breathing room.
							//.useInteractiveGuideline(true)  //We want nice looking tooltips and a guideline!
							//.transitionDuration(350)  //how fast do you want the lines to transition?
							//.showLegend(true)       //Show the legend, allowing users to turn on/off line series.
							//.showYAxis(true)        //Show the y-axis
							//.showXAxis(true)        //Show the x-axis
		//var chart = nv.models.lineWithFocusChart();

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
