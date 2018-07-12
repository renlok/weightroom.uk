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
.faq-group, .faq-group p, .faq-group pre { margin-left: 10px; }
</style>
@endsection

@section('content')
<h2>Entering a workout log</h2>
<div class="faq-group">
<h3>I am so confused what is any of this?</h3>
  <p>The best way to get started with entering a workout is click <span class="text-primary">Track</span> at the top of the page then click <span class="text-primary">Formatting help</span> which gives you a basic format to use to get started</p>
<h3>Do I always have to enter a unit? i.e. 50 kg or 15 minutes</h3>
  <p>Short answer, no.</p>
  <p>When entering a weighted exercise if you don't enter a unit it will default to your default weight unit which can be set on your <a href="{{ route('userSettings') }}">settings</a> page.</p>
  <p>If entering a timed exercise, as long as you have entered the exercise in the past and the exercise is set as a timed exercise (set via Exercise List -> Exercise -> Edit Exercise -> Change exercise type) then it will default to using <strong>minutes</strong>.</p>
  <p>If entering a distance exercise, same as timed exercises as long as the exercise has been setup correctly already then it will default to using <strong>meters</strong>.</p>
  <p>If you haven't set up your time/distance exercises correctly the system will guess incorrectly that you are entering weights.</p>
<h3>How can I post a timed exercise</h3>
  <p>You can enter it in any of the formats</p>
  <pre><span class="cm-W">15:35</span> <span class="cm-C">normal time format</span></pre>
  <pre><span class="cm-W">4 mins</span> <span class="cm-C">or you can use a unit of time either min, minute, sec, second, hour or hr</span></pre>
  <pre><span class="cm-W">3 hours</span></pre>
<h3>How can I post a distance?</h3>
  <p>You just have to add the unit your measuring the distance in</p>
  <p><pre><span class="cm-W">15 miles</span></pre></p>
  <p><pre class="cm-W">4 meters</pre></p>
  <p><pre class="cm-W">3 km</pre></p>
<h3>Can I put multiple sets with different reps on the same line</h3>
  <p>sure its easy:</p>
  <pre><span class="cm-W">50 kg, 80 kg, 120 kg</span> <span class="cm-R">x 5, 3, 1</span> <span class="cm-C">different weights and different reps</span></pre>
  <pre><span class="cm-W">80 kg</span> <span class="cm-R">x 5, 3, 1</span> <span class="cm-C">using the same weight</span></pre>
<h3>How han I input my RPE?</h3>
  <p>just add if after the reps/sets with a @ before it</p>
  <pre><span class="cm-W">270 kg</span> <span class="cm-R">x 5</span> <span class="cm-RPE">@9.5</span></pre>
  <pre><span class="cm-W">250 kg</span> <span class="cm-R">x 5</span> <span class="cm-S">x 3</span> <span class="cm-RPE">@7</span></pre>
</div>
<h2>Exercises</h2>
<div class="faq-group">
<h3>How can I rename an exercise?</h3>
  <p>Click <span class="text-primary">Exercise List</span> and find the exercise you want to edit and click on it. On this page click <span class="text-primary">edit exercise</span></p>
<h3>If I rename an exercise will it update all my logs?</h3>
  <p>yes</p>
</div>
<h2>Other</h2>
<div class="faq-group">
<h3>What does INoL mean?</h3>
  <p>INoL stands for Intensity and number of lifts. It gives a method of tracking intensity of either an entire workout, or for a single exercise where different set and rep ranges have been used.
  Using the equation reps/(100-intensity) a number is calculated for each set and added together, this final value will be the INoL.
    The INoL to aim for for a single exercise in a single workout are 0.4-2. For more information <a href="https://www.powerliftingwatch.com/files/prelipins.pdf">this paper</a> goes into more detail.</p>
<h3>My sinclair/wilks graphs aren't showing anything?</h3>
  <p>First make sure you have set which exercises to use in your <a href="{{ route('userSettings') }}">settings</a> page.</p>
  <p>Secondly make sure you have actually entered some logs using those exercises. We can't know how strong you are if you don't tell us.</p>
<h3>Tagging users or logs</h3>
  <p>You can tag other users in logs or comments like this.</p>
  <pre><span class="cm-C">@username</span></pre>
  <p>You can create a link to a log by adding a usertag and adding the date of the log to it.</p>
  <pre><span class="cm-C">@renlok:2018-02-23</span></pre>
<h3>Is there a list of upcoming features?</h3>
  <p>Sure checkout our <a href="{{ route('plans') }}">todo page</a></p>
</div>

@endsection

@section('endjs')
@endsection
