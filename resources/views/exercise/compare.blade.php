@extends('layouts.master')

@section('title', 'Compare Exercises')

@section('headerstyle')
<link href="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.6/nv.d3.min.css" rel="stylesheet">
<style>
#prHistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
</style>
@endsection

@section('content')
<h2>Compare Exercises</h2>
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
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.3/moment.min.js" charset="utf-8"></script>
<script src="{{ mix('js/graphing.js') }}" charset="utf-8"></script>
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
@foreach ($exercise_names as $table_name => $graph_name)
		var dataset{{ $table_name }} = [];
@endforeach
@foreach ($records as $item)
	@foreach ($exercise_names as $table_name => $graph_name)
        @if ($item->exercise_name == $graph_name)
        dataset{{ $table_name }}.push({x: moment('{{ $item->log_date }}','YYYY-MM-DD').toDate(), y: {{ $item->pr_value }}, shape:'circle'});
        @endif
	@endforeach
@endforeach
@foreach ($exercise_names as $table_name => $graph_name)
		prHistoryChartData.push({
			values: dataset{{ $table_name }},
			key: '{{ str_replace("'", "\'", $graph_name) }}'
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
            if (width > 1150)
            {
                width = 1150;
            }
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
