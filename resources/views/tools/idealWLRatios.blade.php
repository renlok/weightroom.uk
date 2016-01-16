@extends('layouts.master')

@section('title', 'Weightlifting Ratio Calculator')

@section('headerstyle')
<style>

</style>
@endsection

@section('content')
<h2>Weightlifting Ratios Calculator</h2>
<p>This tool calculates the ideal maxes for the different weightlifting exercises.</p>
<p>As these are ideal values they should be taken with a pinch of salt, but they can give you an idea of your weaknesses.</p>
<form>
  <div class="form-group">
    <label for="benchmarklift">Benchmark Lift:</label>
	<select class="form-control" id="benchmarklift">
	  <option value="wlt">Total</option>
	  <option value="wls">Snatch</option>
	  <option value="wlcnj">Clean and Jerk</option>
	  <option value="wlps">Power Snatch</option>
	  <option value="wlpc">Power Clean</option>
	  <option value="wlpj">Power Jerk</option>
	  <option value="wlfs">Front Squat</option>
	  <option value="wlbs">Back Squat</option>
	  <option value="wlp">Pull</option>
	</select>
  </div>
  <div class="form-group">
    <label for="liftrm">Lift 1RM:</label>
    <input type="text" class="form-control" id="liftrm" placeholder="Weight">
  </div>
  <button type="button" class="btn btn-default" id="calculate">Calculate</button>
</form>
<div id="wlratios" style="display:none;">
    <h2>Estimated Max Lifts:</h2>
	<p>Total: <span id="wlt"></span></p>
	<p>Snatch: <span id="wls"></span></p>
	<p>Clean and Jerk: <span id="wlcnj"></span></p>
	<p>Power Snatch: <span id="wlps"></span></p>
	<p>Power Clean: <span id="wlpc"></span></p>
	<p>Power Jerk: <span id="wlpj"></span></p>
	<p>Front Squat: <span id="wlfs"></span></p>
	<p>Back Squat: <span id="wlbs"></span></p>
	<p>Pull: <span id="wlp"></span></p>
</div>
<small class="text-muted">Ratios lovingly stolen from the <a href="http://www.qwa.org/Resources/Calculators.aspx">Queensland weightlifting association</a></small>
@endsection

@section('endjs')
<script>
$('#calculate').click(function(){
	if ($('#wlratios').is(":hidden") == true)
	{
		$('#wlratios').show();
	}
	var bmlift = $('#benchmarklift option:selected').attr('value');
	var liftrm = $('#liftrm').val();
	var ratio = {};
	ratio.wlt = 1.39;
	ratio.wls = 0.62;
	ratio.wlcnj = 0.77;
	ratio.wlps = 0.51;
	ratio.wlpc = 0.63;
	ratio.wlpj = 0.73;
	ratio.wlfs = 0.86;
	ratio.wlbs = 1;
	ratio.wlp = 1.04;
	// get squat value
	var squat = liftrm/ratio[bmlift];
	// set all values
	$('#wlt').text(Math.round(squat*ratio.wlt));
	$('#wls').text(Math.round(squat*ratio.wls));
	$('#wlcnj').text(Math.round(squat*ratio.wlcnj));
	$('#wlps').text(Math.round(squat*ratio.wlps));
	$('#wlpc').text(Math.round(squat*ratio.wlpc));
	$('#wlpj').text(Math.round(squat*ratio.wlpj));
	$('#wlfs').text(Math.round(squat*ratio.wlfs));
	$('#wlbs').text(Math.round(squat));
	$('#wlp').text(Math.round(squat*ratio.wlp));
});
</script>
@endsection
