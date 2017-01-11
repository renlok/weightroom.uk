@extends('layouts.master')

@section('title', $exercise_name . ' History')

@section('headerstyle')
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
</style>
@endsection

@section('content')
<h2>{{ $exercise_name }}</h2>
<small><a href="{{ route('listExercises') }}">&larr; Back to list</a></small> | <small><a href="{{ route('viewExercise', ['exercise_name' => $exercise_name]) }}">&larr; Back to exercise</a></small>

@if ($exercise_count > 1)
<div id="HistoryChart">
    <svg></svg>
</div>
@endif

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
@if ($exercise_count > 1)
<script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.17/d3.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.5/nv.d3.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js" charset="utf-8"></script>
<script>
    function HistoryData() {
		var HistoryChartData = [];
@foreach ($graph_names as $table_name => $graph_name)
		var dataset{{ $table_name }} = [];
@endforeach
@foreach ($log_exercises as $item)
		@foreach ($graph_names as $table_name => $graph_name)
			dataset{{ $table_name }}.push({x: moment('{{ $item->log_date }}','YYYY-MM-DD').toDate(), y: {{ (($table_name == 'logex_1rm' || $table_name == 'logex_volume') ? Format::correct_weight($item->$table_name) : $item->$table_name) * $scales[$table_name] }}, shape:'circle'});
		@endforeach
@endforeach
@foreach ($graph_names as $table_name => $graph_name)
		HistoryChartData.push({
			values: dataset{{ $table_name }},
			key: '{{ $graph_name }}',
	@if ($table_name == 'logex_reps')
			color: '#a6bf50'
	@elseif ($table_name == 'logex_sets')
			color: '#56c5a6'
	@elseif ($table_name == 'logex_1rm')
			color: '#765dcb'
	@elseif ($table_name == 'logex_time')
			color: '#b84a66'
	@elseif ($table_name == 'logex_distance')
			color: '#b84a67'
	@else
			color: '#b84a68'
	@endif
		});
@endforeach
		return HistoryChartData;
    }

    nv.addGraph(function() {
        var chart = nv.models.lineWithFocusChart();
		chart.margin({left: -50});
		chart.tooltip.contentGenerator(function (obj)
		{
			//console.log(key);
			var units = '{{ $user->user_unit }}';
			var point_value = obj.point.y;
			if (obj.point.color == '#b84a68')
				var tool_type = 'Volume';
			if (obj.point.color == '#b84a66')
			{
				var tool_type = 'Time';
				point_value = Math.round((point_value / 3600) * 100) / 100;
				units = 'h';
			}
			if (obj.point.color == '#b84a67')
			{
				var tool_type = 'Distance';
				point_value = Math.round((point_value / 1000) * 100) / 100;
				units = 'km';
			}
			if (obj.point.color == '#a6bf50')
			{
				var tool_type = 'Total reps';
				point_value = Math.round(point_value / {{ $scales['logex_reps'] }});
				units = '';
			}
			if (obj.point.color == '#56c5a6')
			{
				var tool_type = 'Total sets';
				point_value = Math.round(point_value / {{ $scales['logex_sets'] }});
				units = '';
			}
			if (obj.point.color == '#765dcb')
			{
				var tool_type = '1RM';
				point_value = (point_value / {{ $scales['logex_1rm'] }}).toFixed(2);
			}
			return '<pre><strong>' + moment(obj.point.x).format('DD-MM-YYYY') + '</strong><br>' + tool_type + ': ' + point_value + units + '</pre>';
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
@endif
@endsection
