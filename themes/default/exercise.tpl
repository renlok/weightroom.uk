<script src="https://code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<link href="http://nvd3.org/assets/css/nv.d3.css" rel="stylesheet">
<script src="http://nvd3.org/assets/js/nv.d3.js"></script>


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
        var chart = nv.models.lineChart()
							.margin({left: 100})  //Adjust chart margins to give the x-axis some breathing room.
							.useInteractiveGuideline(true)  //We want nice looking tooltips and a guideline!
							.transitionDuration(350)  //how fast do you want the lines to transition?
							.showLegend(true)       //Show the legend, allowing users to turn on/off line series.
							.showYAxis(true)        //Show the y-axis
							.showXAxis(true)        //Show the x-axis

        chart.xAxis
            .axisLabel('Date')
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        chart.yAxis
            .axisLabel('Weight')
            .tickFormat(d3.format('.02f'));

        var data = prHistoryData();
        d3.select('#prHistoryChart svg')
            .datum(data)
            .transition().duration(500)
            .call(chart);

        nv.utils.windowResize(chart.update);

        return chart;
    });

    $(function()
    {
        $('#prHistoryChart .nv-lineChart circle.nv-point').attr("r", "3.5");
    });
</script>


<h1>{EXERCISE}</h1>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>1RM</td>
    <td>2RM</td>
    <td>3RM</td>
    <td>4RM</td>
    <td>5RM</td>
    <td>6RM</td>
    <td>7RM</td>
    <td>8RM</td>
    <td>9RM</td>
    <td>10RM</td>
  </tr>
  <tr>
    <td>{PR_DATA(1)}</td>
    <td>{PR_DATA(2)}</td>
    <td>{PR_DATA(3)}</td>
    <td>{PR_DATA(4)}</td>
    <td>{PR_DATA(5)}</td>
    <td>{PR_DATA(6)}</td>
    <td>{PR_DATA(7)}</td>
    <td>{PR_DATA(8)}</td>
    <td>{PR_DATA(9)}</td>
    <td>{PR_DATA(10)}</td>
  </tr>
</table>

<div id="prHistoryChart">
    <svg></svg>
</div>
