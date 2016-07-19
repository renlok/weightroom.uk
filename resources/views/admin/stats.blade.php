@extends('layouts.master')

@section('title', 'Admin: Stats')

@section('headerstyle')
<link href="{{ asset('css/nv.d3.css') }}" rel="stylesheet">
<style>
#statsGraph .nv-lineChart circle.nv-point {
  fill-opacity: 2;
}
</style>
@endsection

@section('content')
<h2>Admin Land: stats</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

<div id="statsGraph">
    <svg></svg>
</div>
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.14/d3.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js" charset="utf-8"></script>
<script src="{{ asset('js/nv.d3.js') }}"></script>

<script>
    function getStatsData() {
		var total_users = [];
		var onem_active_users = [];
		var threem_active_users = [];
		var ever_active_users = [];
		var total_comments = [];
		var total_comment_replys = [];
		var total_logs = [];
@foreach ($stats as $stat)
		total_users.push({x: moment('{{ $stat->gstat_date }}','YYYY-MM-DD').toDate(), y: {{ $stat->total_users }}, shape:'circle'});
		onem_active_users.push({x: moment('{{ $stat->gstat_date }}','YYYY-MM-DD').toDate(), y: {{ $stat->active_users_1m }}, shape:'circle'});
		threem_active_users.push({x: moment('{{ $stat->gstat_date }}','YYYY-MM-DD').toDate(), y: {{ $stat->active_users_3m }}, shape:'circle'});
		ever_active_users.push({x: moment('{{ $stat->gstat_date }}','YYYY-MM-DD').toDate(), y: {{ $stat->ever_active_users }}, shape:'circle'});
		total_comments.push({x: moment('{{ $stat->gstat_date }}','YYYY-MM-DD').toDate(), y: {{ $stat->total_comments }}, shape:'circle'});
		total_comment_replys.push({x: moment('{{ $stat->gstat_date }}','YYYY-MM-DD').toDate(), y: {{ $stat->total_comment_replys }}, shape:'circle'});
		total_logs.push({x: moment('{{ $stat->gstat_date }}','YYYY-MM-DD').toDate(), y: {{ $stat->total_logs }}, shape:'circle'});
@endforeach
		var statsData = [];
		statsData.push({
			values: total_users,
			key: 'Total Users'
		});
		statsData.push({
			values: onem_active_users,
			key: 'onem_active_users'
		});
		statsData.push({
			values: threem_active_users,
			key: 'threem_active_users'
		});
		statsData.push({
			values: ever_active_users,
			key: 'ever_active_users'
		});
		statsData.push({
			values: total_comments,
			key: 'total_comments'
		});
		statsData.push({
			values: total_comment_replys,
			key: 'total_comment_replys'
		});
		statsData.push({
			values: total_logs,
			key: 'total_logs'
		});
		return statsData;
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
        .tickFormat(function(d) { return d3.time.format('%b %y')(new Date(d)); });

    chart.yAxis
        .axisLabel('Something')
        .tickFormat(d3.format('.02f'));

	d3.select('#statsGraph')
		.attr('style', "width: " + width + "px; height: " + height + "px;" );

        var data = getStatsData();
        d3.select('#statsGraph svg')
		.datum(data)
		.transition().duration(500)
		.attr('perserveAspectRatio', 'xMinYMin meet')
		.call(chart);

        nv.utils.windowResize(resizeChart);
        function resizeChart() {
		var width = $(document).width() - 50;
		if (width > 1150)
		{
			width = 1150;
		}
		var height = Math.round(width/2);
		d3.select('#statsGraph')
			.attr('style', "width: " + width + "px; height: " + height + "px;" );
		chart.update();
        }

        return chart;
    });

    $(function()
    {
        $('#statsGraph .nv-lineChart circle.nv-point').attr("r", "3.5");
    });
</script>
@endsection