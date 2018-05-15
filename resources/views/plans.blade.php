@extends('layouts.master')

@section('title', 'Upcoming features')

@section('headerstyle')
@endsection

@section('content')
<h2>The todo list</h2>
<h3>Workout Templates</h3>
<p>Basic workout templates in place, can generate log text for each log</p>
<p>Mostly complete just need to make it more user friendly</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style="min-width: 50em;">
    90%
  </div>
</div>
<h3>Better UX</h3>
<p>The site has a learning curve, I want that to be as low as possible</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="min-width: 30em;">
    50%
  </div>
</div>
<h3>Better mobile experience</h3>
<p>Most users access the site with a mobile device so we need to make sure everything is mobile friendly</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="min-width: 30em;">
    30%
  </div>
</div>

<p>Last Updated: 15th May 2018</p>
@endsection

@section('endjs')
@endsection
