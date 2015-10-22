<h2>{GRAPH_TYPE}</h2>
<h3>Viewing: <!-- IF RANGE eq 0 -->All<!-- ELSE -->Last {RANGE} months<!-- ENDIF --></h3>

<div id="prHistoryChart">
    <svg></svg>
</div>


<p>Range:
<!-- IF RANGE ne 0 --><a href="?page={GRAPH_TYPE_LC}">All</a><!-- ELSE -->All<!-- ENDIF --> |
<!-- IF RANGE ne 12 --><a href="?page={GRAPH_TYPE_LC}&range=12">1 year</a><!-- ELSE -->1 year<!-- ENDIF --> |
<!-- IF RANGE ne 6 --><a href="?page={GRAPH_TYPE_LC}&range=6">6 months</a><!-- ELSE -->6 months<!-- ENDIF --> |
<!-- IF RANGE ne 3 --><a href="?page={GRAPH_TYPE_LC}&range=3">3 months</a><!-- ELSE -->3 months<!-- ENDIF --> |
<!-- IF RANGE ne 1 --><a href="?page={GRAPH_TYPE_LC}&range=1">1 month</a><!-- ELSE -->1 month<!-- ENDIF --></p>

<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<link href="css/nv.d3.css" rel="stylesheet">
<script src="js/nv.d3.js"></script>

<style>
#prHistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
</style>
<script>
    function prHistoryData() {
		var prHistoryChartData = [];
		{GRAPH_DATA}
		return prHistoryChartData;
    }

    nv.addGraph(function() {
		var width = $(document).width() - 50;
		var height = Math.round(width/2);
        var chart = nv.models.<!-- IF MULTICHART -->multiChart()<!-- ELSE -->lineChart()<!-- ENDIF -->;
						chart.margin({left: 100});  //Adjust chart margins to give the x-axis some breathing room.
            chart.showLegend(false);
						<!-- IF MULTICHART eq false -->chart.useInteractiveGuideline(true);  //We want nice looking tooltips and a guideline!
						chart.duration(350);  //how fast do you want the lines to transition?
						chart.showYAxis(true);        //Show the y-axis
						chart.showXAxis(true);        //Show the x-axis
<!-- ENDIF -->
        chart.xAxis
            .axisLabel('Date')
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        <!-- IF MULTICHART -->
        chart.yAxis1
            .axisLabel('Weight')
            .tickFormat(d3.format('.02f'));
        chart.yAxis2
            .axisLabel('{GRAPH_TYPE}')
            .tickFormat(d3.format('.02f'));
        <!-- ELSE -->
        chart.yAxis
            .axisLabel('Weight')
            .tickFormat(d3.format('.02f'));
        <!-- ENDIF -->

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
