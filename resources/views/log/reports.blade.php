@extends('layouts.master')

@section('title', 'Workout Reports')

@section('headerstyle')
<link href="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.3/nv.d3.min.css" rel="stylesheet">
<style>
svg {
    margin: 0px;
    padding: 0px;
    height: 100%;
    width: 100%;
}
</style>
@endsection

@section('content')
<h2>Workout Reports</h2>

<div class="container">
	<div class="row">
		<div class="col-md-6">
			<select class="form-control reportform" name="view_type" id="view_type">
				<option value="volume">Volume</option>
				<option value="intensity">Intensity</option>
				<option value="setsweek">Sets/Week</option>
				<option value="workoutsweek">Workouts/Week</option>
			</select>
		</div>
		<div class="col-md-6">
			<p><input type="checkbox" name="ignore_warmups" class="reportform" value="1" aria-label="Ignore Warmups"> Ignore Warmups</p>
			<p><input type="checkbox" name="view_horizontal" id="view_horizontal" value="1" aria-label="View Horizontal"> View Horizontal</p>
		</div>
	</div>
	<div>
		<label for="n">Limit to</label>
		<select class="form-control reportform" name="exercise_view" id="exercise_view">
			<option value="everything">Everything</option>
			<option value="powerlifting">Powerlifting</option>
			<option value="weightlifting">Weightlifting</option>
		@foreach ($exercises as $exercise)
	        <option value="{{ $exercise->exercise_id }}" {{ (strtolower($exercise->exercise_name == old('exercise_view')) ? 'selected' : '') }}>{{ $exercise->exercise_name }}</option>
	    @endforeach
		</select>
	</div>
	<div>
		<label for="n">Moving Average</label>
		<select class="form-control" id="n" name="n">
		  <option value="0" {{ old('n') == 0 ? 'selected' : '' }}>Disable</option>
		  <option value="3" {{ old('n') == 3 ? 'selected' : '' }}>3</option>
		  <option value="5" {{ old('n') == 5 ? 'selected' : '' }}>5</option>
		  <option value="7" {{ old('n') == 7 ? 'selected' : '' }}>7</option>
		  <option value="9" {{ old('n') == 9 ? 'selected' : '' }}>9</option>
		</select>
	</div>
</div>

<div id="reportChart">
    <svg></svg>
</div>
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.14/d3.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.3/nv.d3.min.js"></script>

<script>
    var chart = null;
    var stored_data = [];
    var data_length = 0;
    var maxY = 0;
    var key_label = '{{ $key_label }}';
    var unit = 'kg';
    callAjax();
    function prHistoryData(raw_data, ma) {
		var prHistoryChartData = [];
		var dataset = [];
        $.each(raw_data, function(date, value) {
            if (maxY < value) maxY = value;
            dataset.push({x: moment(date,'YYYY-MM-DD').toDate(), y: value, shape:'circle'});
            minDate = date;
        });
		prHistoryChartData.push({
			values: dataset,
			"bar": true,
			key: key_label
		});
        if (ma > 0)
        {
    		prHistoryChartData.push({
    			values: simpleMovingAverage(dataset, ma),
    			key: 'Moving Average'
    		});
        }
		return prHistoryChartData;
    }

    function updateGraph() {
        // clean old graph
        if (chart != null)
            d3.select("#reportChart svg").datum([]).transition().duration(500).call(chart);
        d3.select("#reportChart svg").remove();
        d3.select("#reportChart").append("svg");
        nv.addGraph(function() {
    		var width = $(document).width() - 50;
            if (width > 1150)
            {
                width = 1150;
            }
    		var height = Math.round(width/2);
            var chart = nv.models.linePlusBarChart()
    			.margin({top: 30, right: 60, bottom: 50, left: 70})
                .color(d3.scale.category10().range())
                .useVoronoi(false) // not working so lets disable it for now
                .clipEdge(true)
                .width(width).height(height);

    		chart.noData("Not enough data to generate Report");

    	    chart.xAxis.tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); }).showMaxMin(true);
            chart.x2Axis.tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); }).showMaxMin(true);
    	    chart.y1Axis.tickFormat(yTickFormat).showMaxMin(true);
            chart.y2Axis.tickFormat(yTickFormat).showMaxMin(true);
            chart.y3Axis.tickFormat(yTickFormat).showMaxMin(true);
            chart.y4Axis.tickFormat(yTickFormat).showMaxMin(true);

            function yTickFormat(d) {
                var suffix = '';
                if (unit)
                {
                    suffix = ' ' + unit;
                }
                return d3.format(',.2r')(d) + suffix;
            }

    		d3.select('#reportChart')
    			.attr('style', "width: " + width + "px; height: " + height + "px;" );

            maxY = 0;
            var sorted_data = prHistoryData(stored_data, $("#n").find(":selected").val());

            chart.bars.forceY([0, maxY]);
            chart.bars2.forceY([0, maxY]);
            chart.lines.forceY([0, maxY]);
            chart.lines2.forceY([0, maxY]);

            d3.select('#reportChart svg')
    			.datum(sorted_data)
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
    			d3.select('#reportChart')
    				.attr('style', "width: " + width + "px; height: " + height + "px;" );
    			chart.update();
            }

            return chart;
        });
    }

    $(".reportform").change(function() {
        callAjax();
    });

    function callAjax()
    {
        $.ajax({
            url: '{{ route('ajaxPullReports') }}',
            method: "POST",
            data: {
                view_type: $("#view_type").find(":selected").val(),
                ignore_warmups: $("#ignore_warmups:checked").length,
                exercise_view: $("#exercise_view").find(":selected").val(),
                '_token': '{!! csrf_token() !!}'
            },
            dataType: "json"
        }).done(function(data) {
            key_label = getKeyLabal($("#view_type").find(":selected").val());
            stored_data = data;
            data_length = Object.size(stored_data);
            updateGraph(data);
        });
    }

    $("#n").change(function() {
        updateGraph(stored_data);
    });

    $("#view_horizontal").change(function() {
        // TODO
    });

    function getKeyLabal(view_type) {
        if (view_type == 'setsweek') {
            unit = 'sets';
            return 'Sets/Week';
        }
        else if (view_type == 'workoutsweek') {
            unit = 'workouts';
            return 'Workouts/Week';
        }
        else if (view_type == 'intensity') {
            unit = '';
            return 'Intensity';
        }
        else
        {
            unit = 'kg';
            return 'Volume';
        }
    }

    function simpleMovingAverage(values, n) {
        var return_data = [],
            counter = 0,
            selection = [];
        $.each(values, function(date, value) {
            counter++;
            selection[counter % n] = value;
            if (counter >= n) {
                var sum = 0,
                    value_date = 0;
                $.each(selection, function(n, value) {
                    sum += value.y;
                    value.x > value_date && (value_date = value.x)
                });
                return_data.push({
                    x: value_date,
                    y: sum / n
                })
            }
        });
        return return_data
    }

    Object.size = function(obj) {
        var size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) size++;
        }
        return size;
    };
</script>
@endsection
