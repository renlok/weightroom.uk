<style>
.leftspace {
	margin-left: 10px;
}
</style>

<h2>{EXERCISE}</h2>
<small><a href="?page=exercise&do=list">&larr; Back to list</a></small> | <small><a href="?page=exercise&ex={EXERCISE}">&larr; Back to exercise</a></small>

<div id="HistoryChart">
    <svg></svg>
</div>

<div class="panel-group margintb" id="workouthistory" role="tablist" aria-multiselectable="true">
<!-- BEGIN items -->
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading{items.LOG_DATE}">
      <h4 class="panel-title">
        <a class="collapsed" data-toggle="collapse" data-parent="#workouthistory" href="#collapse{items.LOG_DATE}" aria-expanded="false" aria-controls="collapse{items.LOG_DATE}">
          {items.LOG_DATE}
        </a>
      </h4>
    </div>
    <div id="collapse{items.LOG_DATE}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{items.LOG_DATE}">
      <div class="panel-body">
        <p class="logrow">Volume: <span class="heavy">{items.VOLUME}</span>{WEIGHT_UNIT} - Reps: <span class="heavy">{items.REPS}</span> - Sets: <span class="heavy">{items.SETS}</span> - Avg. Intensity: <span class="heavy">{items.AVG_INT} <!-- IF AVG_INTENSITY_TYPE eq 0 -->%<!-- ELSEIF AVG_INTENSITY_TYPE eq 1 -->{WEIGHT_UNIT}<!-- ENDIF --></span></p>
		<table class="table">
		<tbody>
		<!-- BEGIN sets -->
			<tr<!-- IF items.sets.IS_PR --> class="alert alert-success"<!-- ENDIF --><!-- IF items.sets.REPS eq 0 --> class="alert alert-danger"<!-- ENDIF -->>
				<td class="tdpr">
					<!-- IF items.sets.IS_PR --><span class="glyphicon glyphicon-star" aria-hidden="true"></span><!-- ELSE -->&nbsp;<!-- ENDIF -->
				</td>
				<td class="logrow">
					<!-- IF items.sets.REPS eq 0 --><del><!-- ENDIF --><span class="heavy">{items.sets.WEIGHT}</span><!-- IF items.sets.SHOW_UNIT -->{WEIGHT_UNIT}<!-- ENDIF --> x <span class="heavy">{items.sets.REPS}</span> x <span class="heavy">{items.sets.SETS}</span><!-- IF items.sets.REPS eq 0 --></del><!-- ELSEIF items.sets.REPS gt 1 && items.sets.SHOW_UNIT --> <small class="leftspace"><i>&#8776; {items.sets.EST1RM} {WEIGHT_UNIT}</i></small><!-- ENDIF --><!-- IF items.sets.RPES ne NULL --> @ {items.sets.RPES}<!-- ENDIF -->
					<!-- IF items.sets.COMMENT ne '' --><div class="well well-sm">{items.sets.COMMENT}</div><!-- ENDIF -->
				</td>
				<td class="tdpr2">
					<!-- IF items.sets.IS_PR --><span class="heavy">{items.sets.REPS} RM</span><!-- ELSE -->&nbsp;<!-- ENDIF -->
				</td>
			</tr>
		<!-- END sets -->
			<tr>
				<td colspan="3">{items.COMMENT}</td>
			</tr>
		</tbody>
		</table>
			<small><a href="?do=view&page=log&date={items.LOG_DATE}">View log</a></small>
      </div>
    </div>
  </div>
<!-- END items -->
</div>

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
			if (key.point.color == '#765dcb')
			{
				var tool_type = '1RM';
				point_value = (point_value / {RM_SCALE}).toFixed(2);
			}
			if (key.point.color == '#907fcc')
			{
				var tool_type = 'Average Intensity';
				point_value = Math.round(point_value / {AI_SCALE});
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
