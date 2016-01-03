@section('content')
<h2>{{ $type }}</h2>
<h3>Viewing: @if($range == 0) All @else Last {{ $range }} months @endif</h3>
<p>{{ $message }}</p>

<div id="prHistoryChart">
    <svg></svg>
</div>

<p>Range:
@if($range != 0)
	<a href=" {{ route('bodyweightGraph', ['range' => 0]) }} ">All</a>
@else
	All
@endif |
@if($range != 12)
	<a href=" {{ route('bodyweightGraph', ['range' => 12]) }} ">1 year</a>
@else
	1 year
@endif |
@if($range != 6)
	<a href=" {{ route('bodyweightGraph', ['range' => 6]) }} ">6 months</a>
@else
	6 months
@endif |
@if($range != 0)
	<a href=" {{ route('bodyweightGraph', ['range' => 3]) }} ">3 months</a>
@else
	3 months
@endif |
@if($range != 0)
	<a href=" {{ route('bodyweightGraph', ['range' => 1]) }} ">1 month</a>
@else
	1 month
@endif </p>
@endsection

@section('endjs')
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<link href="{{ asset('css/nv.d3.css') }}" rel="stylesheet">
<script src="{{ asset('js/nv.d3.js') }}"></script>

<style>
#prHistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
</style>
<script>
    function prHistoryData() {
		var prHistoryChartData = [];
@foreach ($graphs as $graph_name => $graph_data)
		var dataset = [];
	@foreach ($graph_data as $data)
		dataset.push({x: moment('{{ $data->log_date }}','YYYY-MM-DD').format(), y: {{ $data->log_weight }}, shape:'circle'});
	@endforeach
		prHistoryChartData.push({
			values: dataset,
			key: '{{ $graph_name }}'
		});
@endforeach
		{{ $graph_data }}
		return prHistoryChartData;
    }

    nv.addGraph(function() {
		var width = $(document).width() - 50;
		var height = Math.round(width/2);
        var chart = nv.models.lineChart();
			chart.margin({left: 100});  //Adjust chart margins to give the x-axis some breathing room.
			chart.showLegend(false);
			chart.useInteractiveGuideline(true);  //We want nice looking tooltips and a guideline!
			chart.duration(350);  //how fast do you want the lines to transition?
			chart.showYAxis(true);        //Show the y-axis
			chart.showXAxis(true);        //Show the x-axis
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
