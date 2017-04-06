@extends('layouts.master')

@section('title', 'Demo')

@section('content')
<div class="row">
<div class="col-md-6">
	<div class="thumbnail">
		<img src="{{ asset('img/screens/log_view.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Bold and clean logs, to keep track of what you have been doing
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/exercise.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Beautiful graphs showing your progress
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/reports.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		View reports of exercise groups, individual exercises or everything. Get the data you need to take control of your training
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/volume.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		See how your workouts have varied over time with a total volume chart
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/exercise_monthly.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Enough data to keep anyone happy
		</div>
	</div>
</div>
<div class="col-md-6">
	<div class="thumbnail">
		<img src="{{ asset('img/screens/log_edit.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Enter your workout quickly and easily with our simple markup
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/compare.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		Easily compare multiple exercises to see how you are progressing and what you need to work on
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/history.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		View history of past exercises
		</div>
	</div>

	<div class="thumbnail">
		<img src="{{ asset('img/screens/pr_history.png') }}" class="img-responsive" alt="Responsive image">
		<div class="caption">
		A record of every PR you have ever hit
		</div>
	</div>
</div>
</div>
@endsection
