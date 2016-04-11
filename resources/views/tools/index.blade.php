@extends('layouts.master')

@section('title', 'Tools')

@section('content')
<h1>Tools</h1>
<div class="row padding">
  <div class="col-md-4">
	<a href="{{ route('totalVolume') }}">Total Volume Graph</a>
	<p>View the total volume of the weight you have lifted in the past</p>
  </div>
  <div class="col-md-4">
	<a href="{{ route('compareExercisesForm') }}">Compare Exercises</a>
	<p>See how your PRs of different exercises have changed over time</p>
  </div>
  <div class="col-md-4">
	<a href="{{ route('searchLog') }}">Search Logs</a>
	<p>Search your log records and find the last time you did something</p>
  </div>
</div>
<div class="row padding">
  <div class="col-md-4">
	<a href="{{ route('bodyweightGraph') }}">Bodyweight Graph</a>
	<p>View how your bodyweight has changed</p>
  </div>
  <div class="col-md-4">
    <a href="{{ route('rmcalculator') }}">Rep Max Calculator</a>
    <p>Calculate what you should be able to lift at different rep ranges</p>
  </div>
  <div class="col-md-4">
    <a href="{{ route('globalGoals') }}">Exercise Goals</a>
    <p>Set different goals for different exercises and see how close you are to hitting them</p>
  </div>
</div>
<h2>Weightlifting</h2>
<small>Like gymnastics with heavy weights.</small>
<div class="row padding">
  <div class="col-md-4">
    <a href="{{ route('sinclairGraph') }}">Sinclair Graph</a>
    <p>View how your Sinclair score has improved over time</p>
  </div>
  <div class="col-md-4">
	<a href="{{ route('wlratios') }}">Weightlifting Ratio Calculator</a>
    <p>Calculate what your other lifts should be based off your current maxes</p>
  </div>
  <div class="col-md-4">
	&nbsp;
  </div>
</div>
<h2>Powerlifting</h2>
<small><em>"I pick things up and put them down"</em></small>
<div class="row padding">
  <div class="col-md-4">
    <a href="{{ route('wilksGraph') }}">Wilks Graph</a>
    <p>View how your Wilks score has improved over time</p>
  </div>
  <div class="col-md-4">
	<a href="{{ route('rpeestimator') }}">RPE Max rep estimator</a>
    <p>Estimate your potential max lift based of RPE values</p>
  </div>
  <div class="col-md-4">
	&nbsp;
  </div>
</div>
@endsection
