@extends('layouts.master')

@section('title', 'Rep Max Calculator')
@section('description', 'Max rep calculator. Calculate your potential max lifts based off past lifts.')

@section('headerstyle')
<style>
.bold {
	font-weight: bold;
}
</style>
@endsection

@section('content')
<h2>Rep Max Calculator</h2>
<p>Calculate your rep max values of any lift. Simply enter how much you lifted and for how many reps and we'll do the hard maths for you.</p>
<p>Disclaimer: These may not be perfectly acurate but they can be used as a good reference point.</p>
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
				<option value="compare">Compare All</option>
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
				<th>&nbsp;</th>
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
		<tbody id="rmbody">

		</tbody>
	</table>
</div>
<p>We offer seven possible formulas to calculate the values. They won't all be perfect for everyone, so it is often worth using Compare All to get a range of where your strength may lie.</p>
<p>It is important to point out these formulas are designs with experienced lifters in mind so will be less accurate for novices. They also become much less accurate if using rep ranges over ten.</p>
<p>If you would like to find more details about the formulas used you can check out the <a href="https://en.wikipedia.org/wiki/One-repetition_maximum">Wikipedia page.</a></p>
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
	if (formula != 'compare')
	{
		var formulas = [formula];
	}
	else
	{
		var formulas = ['average','epley','brzycki','lander','lombardi','mayhew','o_conner','wathan'];
	}
	var clean_formulas = {
		average: 'Average',
		epley: 'Epley',
		brzycki: 'Brzycki',
		lander: 'Lander',
		lombardi: 'Lombardi',
		mayhew: 'Mayhew',
		o_conner: 'O\'Conner',
		wathan: 'Wathan'
	};

	$("#rmbody").html('');
	for (var i = 0; i < formulas.length; i++)
	{
		var rep_maxes = getRMValues(reps, weight, formulas[i]);
		// set all values
		var extra_class = (formulas[i] == 'average')?' class="active"':'';
		$("#rmbody").append('<tr'+extra_class+'><td class="formula">'+clean_formulas[formulas[i]]+'</td><td class="1rm">'+rep_maxes[0]+'</td><td class="2rm">'+rep_maxes[1]+'</td><td class="3rm">'+rep_maxes[2]+'</td><td class="4rm">'+rep_maxes[3]+'</td><td class="5rm">'+rep_maxes[4]+'</td><td class="6rm">'+rep_maxes[5]+'</td><td class="7rm">'+rep_maxes[6]+'</td><td class="8rm">'+rep_maxes[7]+'</td><td class="9rm">'+rep_maxes[8]+'</td><td class="10rm">'+rep_maxes[9]+'</td></tr>');
	}

	// set bold
	if (reps <= 10)
	{
		$( "." + reps + "rm" ).addClass( "bold" );
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
