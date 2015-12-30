@extends('layouts.master')

@section('title', 'Tools')

@section('content')
<h1>Tools</h1>
<div class="row padding">
  <div class="col-md-4">
	<a href="{{ route('totalVolume') }}">Total volume graph</a>
	<p>View the total volume of the weight you have lifted in the past</p>
  </div>
  <div class="col-md-4">
	<a href="{{ route('compareExercisesForm') }}">Compare Exercises</a>
	<p>See how your PRs of different exercises have changed over time</p>
  </div>
  <div class="col-md-4">
	<a href="{{ route('searchLog') }}">Search logs</a>
	<p>Search your log records and find the last time you did something</p>
  </div>
</div>
<div class="row padding">
  <div class="col-md-4">
	<a href="{{ route('bodyweightGraph') }}">Bodyweight graph</a>
	<p>View how your bodyweight has changed</p>
  </div>
  <div class="col-md-4">
	<a href="{{ route('wilksGraph') }}">Wilks Graph</a>
	<p>View how your Wilks score has improved over time</p>
  </div>
  <div class="col-md-4">
	<a href="{{ route('sinclairGraph') }}">Sinclair Graph</a>
	<p>View how your Sinclair score has improved over time</p>
  </div>
</div>
<div class="row padding">
  <div class="col-md-4">
	&nbsp;
  </div>
  <div class="col-md-4">
	&nbsp;
  </div>
  <div class="col-md-4">
	&nbsp;
  </div>
</div>
@endsection
