@extends('layouts.master')

@section('title', 'FAQ')

@section('headerstyle')
<style>
.cm-ENAME { color:#3338B7;}
.cm-W, .cm-WW { color:#337AB7;}
.cm-R, .cm-RR { color:#B7337A;}
.cm-S, .cm-SS { color:#7AB733;}
.cm-RPE, .cm-RPERPE { color: #D70;}
.cm-C { color:#191919; font-style: italic; }
.cm-error{ text-decoration: underline; background:#f00; color:#fff !important; }
.cm-YT { background: #4C8EFA; color:#fff !important;}
</style>
@endsection

@section('content')
<h2>Entering a workout log</h2>
<h3>I am so confused what is any of this?</h3>
  <p>The best way to get started with entering a workout is click <span class="text-primary">Track</span> at the top of the page then click <span class="text-primary">Formatting help</span> which gives you a basic format to use to get started</p>
<h3>How can I post a timed exercise</h3>
  <p>You can enter it in any of the formats</p>
  <p><pre><span class="cm-W">15:35</span> <span class="cm-C">normal time format</span></pre></p>
  <p><pre><span class="cm-W">4 mins</span> <span class="cm-C">or you can use a unit of time either min, minute, sec, second, hour or hr</span></pre></p>
  <p><pre><span class="cm-W">3 hours</span></pre></p>
<h3>How can I post a distance?</h3>
  <p>You just have to add the unit your measuring the distance in</p>
  <p><pre><span class="cm-W">15 miles</span></pre></p>
  <p><pre class="cm-W">4 meters</pre></p>
  <p><pre class="cm-W">3 km</pre></p>
<h3>Can I put multiple sets with different reps on the same line</h3>
  <p>sure its easy:</p>
  <p><pre><span class="cm-W">50 kg, 80 kg, 120 kg</span> <span class="cm-R">x 5, 3, 1</span> <span class="cm-C">different weights and different reps</span></pre></p>
  <p><pre><span class="cm-W">80 kg</span> <span class="cm-R">x 5, 3, 1</span> <span class="cm-C">using the same weight</span></pre></p>
<h3>How han I input my RPE?</h3>
  <p>just add if after the reps/sets with a @ before it</p>
  <p><pre><span class="cm-W">270 kg</span> <span class="cm-R">x 5</span> <span class="cm-RPE">@9.5</span></pre></p>
<h3></h3>
  <p></p>
<h3></h3>
  <p></p>
<h2>Exercises</h2>
<h3>How can I rename an exercise?</h3>
  <p>Click <span class="text-primary">Exercise List</span> and find the exercise you want to edit and click on it. On this page click <span class="text-primary">edit exercise</span></p>
<h3>If I rename an exercise will it update all my logs?</h3>
  <p>yes</p>

@endsection

@section('endjs')
@endsection
