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
#view_type2 {
    margin: 5px 0;
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
        <option value="repsweek">Reps/Week</option>
        <option value="setsweek">Sets/Week</option>
        <option value="workoutsweek">Workouts/Week</option>
      </select>
      <select class="form-control reportform" name="view_type2" id="view_type2">
        <option value="nothing">Nothing</option>
        <option value="volume">Volume</option>
        <option value="intensity">Intensity</option>
        <option value="setsweek">Sets/Week</option>
        <option value="workoutsweek">Workouts/Week</option>
      </select>
    </div>
    <div class="col-md-6">
      <select class="form-control reportform" name="date_range" id="date_range">
        <option value="all">All</option>
        <option value="lastyear" selected="selected">Last 12 months</option>
        <option value="lasthalf">Last 6 months</option>
        <option value="lastquarter">Last 4 months</option>
        <option value="lastmonth">Last 1 month</option>
      </select>
      <p><input type="checkbox" name="ignore_warmups" id="ignore_warmups" class="reportform margintb" value="1" aria-label="Ignore Warmups"> Ignore Warmups</p>
      <p class="hidden"><input type="checkbox" name="view_horizontal" id="view_horizontal" class="reportform" value="1" aria-label="View Horizontal"> View Horizontal</p>
    </div>
  </div>
  <div>
    <label for="n">Limit to</label>
    <select class="form-control reportform" name="exercise_view" id="exercise_view">
      <option value="everything">Everything</option>
      <option value="powerlifting">Group: Powerlifting</option>
      <option value="weightlifting">Group: Weightlifting</option>
    @foreach ($groups as $group)
          <option value="group:{{ $group->exgroup_id }}" {{ (('group:' . $group->exgroup_id) == old('exercise_view')) ? 'selected' : '' }}>Group: {{ $group->exgroup_name }}</option>
    @endforeach
    @foreach ($exercises as $exercise)
          <option value="{{ $exercise->exercise_id }}" {{ (strtolower($exercise->exercise_name) == strtolower(old('exercise_view'))) ? 'selected' : '' }}>{{ $exercise->exercise_name }}</option>
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
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.5.17/d3.min.js" charset="utf-8"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.3/nv.d3.min.js"></script>

<script>
    var chart = null;
    var stored_data = [];
    var data_length = 0;
    var maxY = 0;
    var key_label = ['volume', ''];
    var unit = ['kg'];
    callAjax();
    function reportData(raw_data, ma) {
        var reportChartData = [];
        for (var i = 0; i < raw_data.length; i++) {
            var dataset = [];
            $.each(raw_data[i], function(date, value) {
                if (maxY < value) maxY = value;
                dataset.push({x: moment(date,'YYYY-MM-DD').toDate(), y: value, shape:'circle'});
                minDate = date;
            });
            reportChartData.push({
                values: dataset,
                "bar": true,
                key: key_label[i],
                type: "bar",
                yAxis: (i + 1)
            });
            if (ma > 0) {
                reportChartData.push({
                    values: simpleMovingAverage(dataset, ma),
                    key: 'Moving Average',
                    type: "line",
                    yAxis: (i + 1)
                });
            }
        }
        return reportChartData;
    }

    function updateGraph() {
        // clean old graph
        if (chart != null)
            d3.select("#reportChart svg").datum([]).transition().duration(500).call(chart);
        d3.select("#reportChart svg").remove();
        d3.select("#reportChart").append("svg");
        var barchart = ($("#view_type2").find(":selected").val() == 'nothing');
        nv.addGraph(function() {
            var width = $(document).width() - 50;
            if (width > 1150)
            {
                width = 1150;
            }
            var height = Math.round(width/2);
            chart = (barchart) ? nv.models.linePlusBarChart().clipEdge(true) : nv.models.multiChart();
            chart.margin({top: 30, right: 60, bottom: 50, left: 70})
                 .color(d3.scale.category10().range())
                 .useVoronoi(false) // not working so lets disable it for now
                 .width(width).height(height);

            chart.noData("Not enough data to generate Report");

            chart.xAxis.tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); }).showMaxMin(true);
            if (barchart) {
                chart.x2Axis.tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); }).showMaxMin(true);
                chart.y1Axis.tickFormat(yTickFormat).showMaxMin(true);
                chart.y2Axis.tickFormat(yTickFormat).showMaxMin(true);
                chart.y3Axis.tickFormat(yTickFormat).showMaxMin(true);
                chart.y4Axis.tickFormat(yTickFormat).showMaxMin(true);
            } else {
                chart.yAxis1.tickFormat(yTickFormat).showMaxMin(true);
                chart.yAxis2.tickFormat(yTickFormatR).showMaxMin(true);
            }

            function yTickFormat(d) {
                return addUnit(d, 0);
            }

            function yTickFormatR(d) {
                return addUnit(d, 1);
            }

            function addUnit(d, u) {
                var suffix = '';
                if (unit[u])
                {
                    suffix = ' ' + unit[u];
                }
                return d3.format(',.2r')(d) + suffix;
            }

            d3.select('#reportChart')
              .attr('style', "width: " + width + "px; height: " + height + "px;" );

            maxY = 0;
            var sorted_data = reportData(stored_data, $("#n").find(":selected").val());

            if (barchart) {
                chart.lines.forceY([0, maxY]);
                chart.bars.forceY([0, maxY]);
            } else {
                chart.lines1.forceY([0, maxY]);
                chart.bars1.forceY([0, maxY]);
            }
            chart.lines2.forceY([0, maxY]);
            chart.bars2.forceY([0, maxY]);

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

            if (!barchart) {
                nv.dispatch.on('render_end', function(newState) {
                    resetBarSize();
                    chart.legend.dispatch.on('legendClick', function(newState) {
                        chart.update();
                        setTimeout(function(){resetBarSize(newState)});
                    });
                });
            }

            return chart;
        });
    }

    function resetBarSize(d1) {
        var w2 = d3.select(".bars2Wrap .nv-bar").attr("width")/2;
        if (!d1) {
            d3.selectAll(".bars1Wrap .nv-bar").style("width", w2);
            d3.selectAll(".bars2Wrap .nv-bar").style("width", w2);
            d3.selectAll(".bars2Wrap .nv-bar")[0].forEach(function(d) {
                var t = d3.transform(d3.select(d).attr("transform")),
                x = t.translate[0] + w2,
                y = t.translate[1];
                d3.select(d).attr("transform", "translate(" + x +"," + y + ")");
            });
        } else if (d1.yAxis == 2 && d1.disabled) {
            d3.selectAll(".bars1Wrap .nv-bar").style("width", w2 * 2);
        } else if (d1.yAxis == 1 && d1.disabled) {
            d3.selectAll(".bars2Wrap .nv-bar").style("width", w2 * 2);
        } else {
            d3.selectAll(".bars1Wrap .nv-bar").style("width", w2);
            d3.selectAll(".bars2Wrap .nv-bar").style("width", w2);
            d3.selectAll(".bars2Wrap .nv-bar")[0].forEach(function(d){
                var t = d3.transform(d3.select(d).attr("transform")),
                x = t.translate[0] + w2,
                y = t.translate[1];
                d3.select(d).attr("transform", "translate(" + x +"," + y + ")");
            });
        }
        return;
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
                view_type2: $("#view_type2").find(":selected").val(),
                ignore_warmups: $("#ignore_warmups:checked").length,
                exercise_view: $("#exercise_view").find(":selected").val(),
                '_token': '{!! csrf_token() !!}'
            },
            dataType: "json"
        }).done(function(data) {
            key_label[0] = getKeyLabal($("#view_type").find(":selected").val(), 0);
            if ($("#view_type2").find(":selected").val() != 'nothing') {
                key_label[1] = getKeyLabal($("#view_type2").find(":selected").val(), 1);
            }
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

    function getKeyLabal(view_type, u) {
        if (view_type == 'setsweek') {
            unit[u] = 'sets';
            return 'Sets/Week';
        }
        else if (view_type == 'workoutsweek') {
            unit[u] = 'workouts';
            return 'Workouts/Week';
        }
        else if (view_type == 'intensity') {
            unit[u] = '';
            return 'Intensity';
        }
        else
        {
            unit[u] = '{{ Auth::user()->user_unit }}';
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
