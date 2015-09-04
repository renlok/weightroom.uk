<link rel="stylesheet" type="text/css" href="css/flatpickr.css"><style>
.leftspace {
	margin-left: 10px;
}
</style>

<h2>Volume graph</h2>

<h3>View a range of dates</h3>
<form class="form-inline" method="get">
  <div class="form-group">
    <label for="from_date">From</label>
    <input type="text" class="form-control" id="from_date" name="from" value="{FROM_DATE}">
  </div>
  <div class="form-group">
    <label for="to_date">Until</label>
    <input type="text" class="form-control" id="to_date" name="to" value="{TO_DATE}">
  </div>
  <input type="hidden" name="page" value="volume">
  <button type="submit" class="btn btn-default">Update</button>
</form>

<div id="HistoryChart">
    <svg></svg>
</div>

<script src="js/flatpickr.js"></script>
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<link href="http://nvd3.org/assets/css/nv.d3.css" rel="stylesheet">
<script src="js/nv.d3.js"></script>
<style>
#HistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
#HistoryChart, svg {
  height: 400px;
}
.nv-axisMaxMin, .nv-y text { display: none; }
</style>
<script>
var date = new Date();
date.setDate(date.getDate() + 1);
var check_in = flatpickr("#from_date", { maxDate: date, dateFormat: 'Y-m-d'});
var check_out = flatpickr("#to_date", { maxDate: date, dateFormat: 'Y-m-d'});

check_in.element.addEventListener("change", function(){
    check_out.set( "minDate" , check_in.element.value );
});

check_out.element.addEventListener("change", function(){
    check_in.set( "maxDate" , check_out.element.value );
});


    function HistoryData() {
		var HistoryChartData = [];
		{GRAPH_DATA}
		return HistoryChartData;
    }

    nv.addGraph(function() {
        var chart = nv.models.lineWithFocusChart();
		chart.margin({left: -50});
		chart.tooltipContent(function(key, y, e, graph)
		{
			//console.log(key);
			var units = '{WEIGHT_UNIT}';
			var point_value = key.point.y;
			if (key.point.color == '#b84a68')
				var tool_type = 'Volume';
			if (key.point.color == '#a6bf50')
			{
				var tool_type = 'Total reps';
				point_value = Math.round(point_value / {REP_SCALE});
				units = '';
			}
			if (key.point.color == '#56c5a6')
			{
				var tool_type = 'Total sets';
				point_value = Math.round(point_value / {SET_SCALE});
				units = '';
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