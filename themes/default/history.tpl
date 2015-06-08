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
        <p class="logrow">Volume: <span class="heavy">{items.VOLUME}</span>kg - Reps: <span class="heavy">{items.REPS}</span> - Sets: <span class="heavy">{items.SETS}</span></p>
		<table class="table">
		<tbody>
		<!-- BEGIN sets -->
			<tr<!-- IF items.sets.IS_PR --> class="alert alert-success"<!-- ENDIF --><!-- IF items.sets.REPS eq 0 --> class="alert alert-danger"<!-- ENDIF -->>
				<td class="tdpr">
					<!-- IF items.sets.IS_PR --><span class="glyphicon glyphicon-star" aria-hidden="true"></span><!-- ELSE -->&nbsp;<!-- ENDIF -->
				</td>
				<td class="logrow">
					<!-- IF items.sets.REPS eq 0 --><del><!-- ENDIF --><span class="heavy">{items.sets.WEIGHT}</span>kg x <span class="heavy">{items.sets.REPS}</span> x <span class="heavy">{items.sets.SETS}</span><!-- IF items.sets.REPS eq 0 --></del><!-- ENDIF -->
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
      </div>
    </div>
  </div>
<!-- END items -->
</div>

<script src="https://code.jquery.com/jquery-2.1.3.min.js" charset="utf-8"></script>
<script src="http://getbootstrap.com/dist/js/bootstrap.min.js" charset="utf-8"></script>
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
<link href="http://nvd3.org/assets/css/nv.d3.css" rel="stylesheet">
<script src="http://nvd3.org/assets/js/nv.d3.js"></script>
<style>
#HistoryChart .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
#HistoryChart, svg {
  height: 400px;
}
</style>
<script>
    function HistoryData() {
		var HistoryChartData = [];
		{GRAPH_DATA}
		return HistoryChartData;
    }

    nv.addGraph(function() {
        //var chart = nv.models.lineWithFocusChart();
							//.margin({left: 100})  //Adjust chart margins to give the x-axis some breathing room.
							//.useInteractiveGuideline(true)  //We want nice looking tooltips and a guideline!
							//.transitionDuration(350)  //how fast do you want the lines to transition?
							//.showLegend(true)       //Show the legend, allowing users to turn on/off line series.
							//.showYAxis(true)        //Show the y-axis
							//.showXAxis(true)        //Show the x-axis
		var chart = nv.models.lineWithFocusChart();

        chart.xAxis
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        chart.x2Axis
            .axisLabel('Date')
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        chart.yAxis
            .axisLabel('Weight')
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