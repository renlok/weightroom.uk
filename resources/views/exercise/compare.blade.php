@extends('layouts.master')

@section('title', 'Compare Exercises')

@section('headerstyle')
<style>
#prHistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
</style>
@endsection

@section('content')
<span id="showhide"><button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span> Hide</button></span>
<div id="compareform" class="hidebox">
	@include('exercise.common.compareform')
</div>

<h1>
    Compare: {{ $exercise1 }}
    @if ($exercise2 != '')
    , {{ $exercise2 }}
    @endif
    @if ($exercise3 != '')
    , {{ $exercise3 }}
    @endif
    @if ($exercise4 != '')
    , {{ $exercise4 }}
    @endif
    @if ($exercise5 != '')
    , {{ $exercise5 }}
    @endif
</h1>

<div id="prHistoryChart">
    <svg></svg>
</div>
@endsection

@section('endjs')
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<link href="{{ asset('css/nv.d3.css') }}" rel="stylesheet">
<script src="{{ asset('js/nv.d3.js') }}"></script>
<script>
	$( document ).ready(function() {
		$("#exlist option").click(function() {
			if (5 <= $(this).siblings(":selected").length) {
				$(this).removeAttr("selected");
			}
		});
		$('#showhide').click(function() {
			$('#compareform').slideToggle('fast');
			$('#showhide').html(function(_,txt) {
				var ret='';

				if ( txt == '<button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> Show</button>' ) {
				   ret = '<button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span> Hide</button>';
				}else{
				   ret = '<button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> Show</button>';
				}
				return ret;
			});
			return false;
		});
	});

    function prHistoryData() {
		var prHistoryChartData = [];
        @foreach ($records as $exercises)
            var dataset = [];
            @foreach ($exercises as $exercise_record)
                dataset.push({x: moment('{{ $date }}','YYYY-MM-DD').toDate(), y: $weight, shape:'circle'});
            @endforeach
            prHistoryChartData.push({
                values: dataset,
                key: '{$rep}{$type_string}'
            });
        @endforeach
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
