@extends('layouts.master')

@section('title', 'Goals')

@section('headerstyle')
@endsection

@section('content')
<h2>Goals</h2>
<p><a href="#">Edit goals</a></p>
<div>
    <div class="form-inline">
      <div class="form-group">
            <select class="form-control goalType" name="goalType" v-on:change="old_goal(goal)" v-model="goal.goal_type">
                <option value="wr">Weight x Rep</option>
                <option value="rm">Estimate 1rm</option>
                <option value="tv">Total volume</option>
                <option value="tr">Total reps</option>
              </select>
            <input type="text" class="form-control" name="valueOne" v-model="goal.goal_value_one">
            <span v-bind:class="{ 'hidden': goal.hidden }"> x
                <input type="text" class="form-control" name="valueTwo" v-model="goal.goal_value_two">
            </span>
      </div>
    </div>
    <div class="progress">
      <div class="progress-bar" role="progressbar" aria-valuenow="@{{ goal.percent }}" aria-valuemin="0" aria-valuemax="100" style="width: @{{ goal.percent }}%;">
        @{{ goal.percent }}%
      </div>
    </div>
</div>
@endsection

@section('endjs')
@endsection
