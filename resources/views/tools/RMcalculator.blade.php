@extends('layouts.master')

@section('title', 'Rep Max Calculator')

@section('headerstyle')
<style>
.bold {
	font-weight: bold;
}
</style>
@endsection

@section('content')
<h2>Rep Max Calculator</h2>
<p>This tool calculates estimated rep maxes based of different formulas.</p>
<p>These may not be perfectly acurate but they can be used as a good reference point.</p>
<div class="container-fluid">
	<div class="form-inline">
		<div class="form-group">
			<label for="benchmarklift">Weight</label>
			<input type="text" class="form-control" id="weight" placeholder="Weight">
			<label for="reps">Reps</label>
			<input type="text" class="form-control" id="reps" placeholder="Reps">
		</div>
		<div class="form-group">
			<label for="formula">Formula</label>
			<select class="form-control" name="formula" id="formula">
				<option value="average">Average</option>
				<option value="epley">Epley</option>
				<option value="brzycki">Brzycki</option>
				<option value="lander">Lander</option>
				<option value="lombardi">Lombardi</option>
				<option value="mayhew">Mayhew</option>
				<option value="o_conner">O'Conner</option>
				<option value="wathan">Wathan</option>
			</select>
		</div>
		<div class="form-group">
			<button type="button" class="btn btn-default" id="calculate">Calculate</button>
		</div>
	</div>
</div>
<div id="displaybox" style="display:none;">
    <table class="table">
		<thead>
			<tr>
				<th>1 RM</th>
				<th>2 RM</th>
				<th>3 RM</th>
				<th>4 RM</th>
				<th>5 RM</th>
				<th>6 RM</th>
				<th>7 RM</th>
				<th>8 RM</th>
				<th>9 RM</th>
				<th>10 RM</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id="1rm"></td>
				<td id="2rm"></td>
				<td id="3rm"></td>
				<td id="4rm"></td>
				<td id="5rm"></td>
				<td id="6rm"></td>
				<td id="7rm"></td>
				<td id="8rm"></td>
				<td id="9rm"></td>
				<td id="10rm"></td>
			</tr>
		</tbody>
	</table>
</div>
@endsection

@section('endjs')
<script>
$('#calculate').click(function(){
	var reps = $('#reps').val();
	if (reps <= 0)
	{
		return false;
	}
	$( ".bold" ).removeClass( "bold" );
	if ($('#displaybox').is(":hidden") == true)
	{
		$('#displaybox').show();
	}
	var formula = $('#formula option:selected').attr('value');
	var weight = $('#weight').val();

	var rep_maxes = getRMValues(reps, weight, formula);

	// set all values
	$('#1rm').text(rep_maxes[0]);
	$('#2rm').text(rep_maxes[1]);
	$('#3rm').text(rep_maxes[2]);
	$('#4rm').text(rep_maxes[3]);
	$('#5rm').text(rep_maxes[4]);
	$('#6rm').text(rep_maxes[5]);
	$('#7rm').text(rep_maxes[6]);
	$('#8rm').text(rep_maxes[7]);
	$('#9rm').text(rep_maxes[8]);
	$('#10rm').text(rep_maxes[9]);

	// set bold
	if (reps <= 10)
	{
		$( "#" + reps + "rm" ).addClass( "bold" );
	}
});

function getRMValues(reps, weight, formula)
{
	// initialise array
	var rep_maxes = new Array(10);
	// no point working this out
	rep_maxes[reps-1] = weight;

	// want the average of all
	if (formula == 'average')
	{
		var epley = calc1rm['epley'](weight, reps);
		var brzycki = calc1rm['brzycki'](weight, reps);
		var lander = calc1rm['lander'](weight, reps);
		var lombardi = calc1rm['lombardi'](weight, reps);
		var mayhew = calc1rm['mayhew'](weight, reps);
		var o_conner = calc1rm['o_conner'](weight, reps);
		var wathan = calc1rm['wathan'](weight, reps);
		if (reps != 1)
		{
			// get 1rm
			rep_maxes[0] = Math.round(((epley + brzycki + lander + lombardi + mayhew + o_conner + wathan) / 7) * 10) / 10;
		}
		for (var i = 1; i < 10; i++)
		{
			if (reps != (i + 1))
			{
				var tepley = calcXrm['epley'](epley, (i + 1));
				var tbrzycki = calcXrm['brzycki'](brzycki, (i + 1));
				var tlander = calcXrm['lander'](lander, (i + 1));
				var tlombardi = calcXrm['lombardi'](lombardi, (i + 1));
				var tmayhew = calcXrm['mayhew'](mayhew, (i + 1));
				var to_conner = calcXrm['o_conner'](o_conner, (i + 1));
				var twathan = calcXrm['wathan'](wathan, (i + 1));
				rep_maxes[i] = Math.round(((tepley + tbrzycki + tlander + tlombardi + tmayhew + to_conner + twathan) / 7) * 10) / 10;
			}
		}
	}
	else
	{
		var temp_1rm = calc1rm[formula](weight, reps);
		if (reps != 1)
		{
			// get 1rm
			rep_maxes[0] = Math.round(temp_1rm * 10) / 10;
		}
		for (var i = 1; i < 10; i++)
		{
			if (reps != (i + 1))
			{
				rep_maxes[i] = Math.round(calcXrm[formula](temp_1rm, (i + 1)) * 10) / 10;
			}
		}
	}

	return rep_maxes;
}

var calc1rm = {
	epley: function(weight, reps) { return weight * (1 + (reps / 30)); },
	brzycki: function(weight, reps) { return weight * (36 / (37 - reps)); },
	lander: function(weight, reps) { return weight * 100 / (101.3 - 2.67123 * reps); },
	lombardi: function(weight, reps) { return weight * Math.pow(reps, 1 / 10); },
	mayhew: function(weight, reps) { return (weight * 100) / (52.2 + (41.9 * Math.exp(-1 * (reps * 0.055)))); },
	o_conner: function(weight, reps) { return weight * (1 + reps * 0.025); },
	wathan: function(weight, reps) { return (weight * 100) / (48.8 + (53.8 * Math.exp(-1 * (reps * 0.075)))); }
};

var calcXrm = {
	epley: function(one_rm, reps) { return one_rm / ((1 + (reps / 30))); },
	brzycki: function(one_rm, reps) { return (one_rm * (37 - reps)) / 36; },
	lander: function(one_rm, reps) { return (one_rm * (101.3 - 2.67123 * reps)) / 100; },
	lombardi: function(one_rm, reps) { return one_rm / (Math.pow(reps, 1 / 10)); },
	mayhew: function(one_rm, reps) { return (one_rm * (52.2 + (41.9 * Math.exp(-1 * (reps * 0.055))))) / 100; },
	o_conner: function(one_rm, reps) { return one_rm / (1 + reps * 0.025); },
	wathan: function(one_rm, reps) { return (one_rm * (48.8 + (53.8 * Math.exp(-1 * (reps * 0.075))))) / 100; }
}
</script>
@endsection
