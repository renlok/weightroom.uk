@extends('layouts.master')

@section('title', 'Demo')

@section('content')
<div class="row">
<div class="col-md-6">
	<div class="thumbnail">
		<img src="{{ asset('img/screens/logs.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Calender view, easily see which days you worked out on
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/chart1.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Easily compare multiple exercises to see how you are progressing across the board
		</div>
	</div>
</div>
<div class="col-md-6">
	<div class="thumbnail">
		<img src="{{ asset('img/screens/exersice.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Colourful logs, to keep track of what you have been doing
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/chart2.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Beautiful graphs showing your progress
		</div>
	</div>
</div>
</div>
@endsection
