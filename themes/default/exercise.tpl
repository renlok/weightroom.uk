<h2>{EXERCISE} <small><!-- IF TYPE eq 'weekly' -->Weekly maxes<!-- ELSE -->PRs<!-- ENDIF --></small></h2>
<h3>Viewing: <!-- IF RANGE eq 0 -->All<!-- ELSE -->Last {RANGE} months<!-- ENDIF --></h3>
<small><a href="?page=exercise&do=list">&larr; Back to list</a></small> | <small><a href="?page=edit_exercise&exercise_name={EXERCISE}">Edit exercise</a></small> | <small><a href="?page=history&ex={EXERCISE}">View history</a></small>

<table width="100%" class="table">
<thead>
  <tr>
    <th>1RM</th>
    <th>2RM</th>
    <th>3RM</th>
    <th>4RM</th>
    <th>5RM</th>
    <th>6RM</th>
    <th>7RM</th>
    <th>8RM</th>
    <th>9RM</th>
    <th>10RM</th>
  </tr>
</thead>
<tbody>
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
  <tr>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(1)}">{TRUE_PR_DATA(1)}</a><!-- ELSE -->{TRUE_PR_DATA(1)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(2)}">{TRUE_PR_DATA(2)}</a><!-- ELSE -->{TRUE_PR_DATA(2)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(3)}">{TRUE_PR_DATA(3)}</a><!-- ELSE -->{TRUE_PR_DATA(3)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(4)}">{TRUE_PR_DATA(4)}</a><!-- ELSE -->{TRUE_PR_DATA(4)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(5)}">{TRUE_PR_DATA(5)}</a><!-- ELSE -->{TRUE_PR_DATA(5)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(6)}">{TRUE_PR_DATA(6)}</a><!-- ELSE -->{TRUE_PR_DATA(6)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(7)}">{TRUE_PR_DATA(7)}</a><!-- ELSE -->{TRUE_PR_DATA(7)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(8)}">{TRUE_PR_DATA(8)}</a><!-- ELSE -->{TRUE_PR_DATA(8)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(9)}">{TRUE_PR_DATA(9)}</a><!-- ELSE -->{TRUE_PR_DATA(9)}<!-- ENDIF --></td>
    <td><!-- IF PR_DATES_TEMP ne 0 --><a href="?do=view&page=log&date={PR_DATES(10)}">{TRUE_PR_DATA(10)}</a><!-- ELSE -->{TRUE_PR_DATA(10)}<!-- ENDIF --></td>
  </tr>
</tbody>
</table>

<div id="prHistoryChart">
    <svg></svg>
</div>

<!-- IF TYPE eq 'weekly' -->
<p>Range: 
<!-- IF RANGE ne 0 --><a href="?page=exercise&ex={EXERCISE}&do=weekly">All</a><!-- ELSE -->All<!-- ENDIF --> | 
<!-- IF RANGE ne 12 --><a href="?page=exercise&ex={EXERCISE}&do=weekly&range=12">1 year</a><!-- ELSE -->1 year<!-- ENDIF --> | 
<!-- IF RANGE ne 6 --><a href="?page=exercise&ex={EXERCISE}&do=weekly&range=6">6 months</a><!-- ELSE -->6 months<!-- ENDIF --> | 
<!-- IF RANGE ne 3 --><a href="?page=exercise&ex={EXERCISE}&do=weekly&range=3">3 months</a><!-- ELSE -->3 months<!-- ENDIF --> | 
<!-- IF RANGE ne 1 --><a href="?page=exercise&ex={EXERCISE}&do=weekly&range=1">1 month</a><!-- ELSE -->1 month<!-- ENDIF --></p>
<p><a href="?page=exercise&ex={EXERCISE}">View Prs</a></p>
<!-- ELSE -->
<p>Range: 
<!-- IF RANGE ne 0 --><a href="?page=exercise&ex={EXERCISE}">All</a><!-- ELSE -->All<!-- ENDIF --> | 
<!-- IF RANGE ne 12 --><a href="?page=exercise&ex={EXERCISE}&range=12">1 year</a><!-- ELSE -->1 year<!-- ENDIF --> | 
<!-- IF RANGE ne 6 --><a href="?page=exercise&ex={EXERCISE}&range=6">6 months</a><!-- ELSE -->6 months<!-- ENDIF --> | 
<!-- IF RANGE ne 3 --><a href="?page=exercise&ex={EXERCISE}&range=3">3 months</a><!-- ELSE -->3 months<!-- ENDIF --> | 
<!-- IF RANGE ne 1 --><a href="?page=exercise&ex={EXERCISE}&range=1">1 month</a><!-- ELSE -->1 month<!-- ENDIF --></p>
<p><a href="?page=exercise&ex={EXERCISE}&do=weekly">View weekly maxes</a></p>
<!-- ENDIF -->



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
