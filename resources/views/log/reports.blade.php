@extends('layouts.master')

@section('title', 'Workout Reports')

@section('headerstyle')

@endsection

@section('content')
<h2>Workout Reports</h2>

<div class="container">
	<div class="row">
		<div class="col-md-6">
			<select class="form-control" name="view_type">
				<option>Volume</option>
				<option>Intensity</option>
				<option>Sets/Week</option>
				<option>Workouts/Week</option>
			</select>
		</div>
		<div class="col-md-6">
			<p><input type="checkbox" name="ignore_warmups" value="1" aria-label="Ignore Warmups"> Ignore Warmups</p>
			<p><input type="checkbox" name="view_horizontal" value="1" aria-label="View Horizontal"> View Horizontal</p>
		</div>
	</div>
	<div>
		<label for="n">Limit to</label>
		<select class="form-control" name="exercise_view">
			<option value="everything">Everything</option>
			<option value="powerlifting">Powerlifting</option>
			<option value="weightlifting">Weightlifting</option>
		@foreach ($exercises as $exercise)
	        <option value="{{ $exercise->exercise_id }}" {{ (strtolower($exercise->exercise_name == $exercise_names) ? 'selected' : '' }}>{{ $exercise->exercise_name }}</option>
	    @endforeach
		</select>
	</div>
	<div>
		<label for="n">Moving Average</label>
		<select class="form-control" id="n" name="n">
		  <option value="0" {{ $n == 0 ? 'selected' : '' }}>Disable</option>
		  <option value="3" {{ $n == 3 ? 'selected' : '' }}>3</option>
		  <option value="5" {{ $n == 5 ? 'selected' : '' }}>5</option>
		  <option value="7" {{ $n == 7 ? 'selected' : '' }}>7</option>
		  <option value="9" {{ $n == 9 ? 'selected' : '' }}>9</option>
		</select>
	</div>
</div>

<div id="reportChart">
    <svg></svg>
</div>
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.14/d3.min.js" charset="utf-8"></script>
<script src="{{ asset('js/nv.d3.js') }}"></script>

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
        var chart = nv.models.linePlusBarChart()
			.margin({left: 100})  //Adjust chart margins to give the x-axis some breathing room.
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
