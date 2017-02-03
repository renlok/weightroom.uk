@extends('layouts.master')

@section('title', 'Upcoming features')

@section('headerstyle')
@endsection

@section('content')
<h2>The todo list</h2>
<h3>Import Workouts</h3>
<p>Mostly working now to upload csv formatted workout logs, needs some more testing before considered complete</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style="min-width: 90em;">
    90%
  </div>
</div>
<h3>Export Workouts</h3>
<p>Allow users to export their data in multiple formats</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">
    0%
  </div>
</div>
<h3>Workout Templates</h3>
<p>Basic workout templates in place, can generate log text for each log</p>
<p>Creating a workout template is very bare bones right now, needs more work and much more prttying up before being public</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="min-width: 50em;">
    50%
  </div>
</div>
<h3>Log tags</h3>
<p>Option to tag other logs or users within a log</p>
<p>This will send a notification to the user and make a link to their account or link to the log</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">
    0%
  </div>
</div>
<h3>Exercise tags</h3>
<p>This will allow exercises to be grouped. Allowing to easily analyse all the exercise within the group together.</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">
    0%
  </div>
</div>
<h3>Better UX</h3>
<p>The site has a learning curve, I want that to be as low as possible</p>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="min-width: 30em;">
    30%
  </div>
</div>

<p>Last Updated: 3rd Febuary 2017</p>
@endsection

@section('endjs')
@endsection
