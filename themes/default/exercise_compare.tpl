<script src="https://code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<link href="http://nvd3.org/assets/css/nv.d3.css" rel="stylesheet">
<script src="http://nvd3.org/assets/js/nv.d3.js"></script>


<style>
#prHistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
#prHistoryChart {
  height: 700px;
}
</style>
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

<span id="showhide"><button type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span> Hide</button></span>
<div id="compareform" class="hidebox">
	<form action="?page=exercise&do=compare" method="get">
	<input type="hidden" name="page" value="exercise">
	<input type="hidden" name="do" value="compare">
	<br>
	<div class="form-group">
		<label for="ex">Exercises to compare <small>max 5</small></label>
		<select name="ex[]" size="10" multiple required id="exlist" class="form-control">
		<!-- BEGIN exercise -->
			<option value="{exercise.EXERCISE}"<!-- IF exercise.SELECTED --> selected<!-- ENDIF -->>{exercise.EXERCISE}</option>
		<!-- END -->
		</select>
	</div>
	<div class="form-group">
		<label for="reps">Reps</label>
		<select name="reps" required class="form-control">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
		</select>
	</div>
	<input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
	<input type="submit" name="action" value="Compare" class="btn btn-default">
	</form>
</div>

<!-- IF B_SELECTED -->
<h1>Compare: {EXERCISE}</h1>
<!-- ENDIF -->
<div id="prHistoryChart">
    <svg></svg>
</div>