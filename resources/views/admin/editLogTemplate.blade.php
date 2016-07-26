@extends('layouts.master')

@section('title', 'Admin: Edit Log Template')

@section('headerstyle')
<style>
#app {
}
.log {
	border-left: 1px solid #262626;
	margin: 3px;
	padding: 0 0 5px 5px;
}
.exercise {
	border-left: 1px solid #4d4d4d;
	margin: 3px;
	padding: 0 0 5px 10px;
}
.logItem {
	border-left: 1px solid #737373;
	margin: 3px;
	padding: 0 0 5px 20px;
}
</style>
@endsection

@section('content')
<h2>Admin Land: Edit Log Template</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

@include('common.flash')
<form action="{{ route('adminAddTemplate') }}" method="post">
	<label for="templateName">Template name</label>
	<input type="text" value="" id="templateName" name="template_name" placeholder="Template name">
	<label for="templateDesc">Template description</label>
	<input type="text" value="" id="templateDesc" name="template_description" placeholder="Template description">
	<label for="templateName">Template type</label>
	<select name="template_type">
		<option value="powerlifting">powerlifting</option>
		<option value="running">running</option>
		<option value="weightlifting">weightlifting</option>
	</select>
	<div id="app">
		<template v-for="(log_index, log) in log_data">
			<div class="log">
				<label>Log</label>
				<input type="text" value="@{{ log.log_name }}" name="log_name[@{{ log_index }}]" placeholder="Log name"><button type="button" v-on:click="deleteLog(log_index)">x</button>
				<input type="text" value="@{{ log.log_week }}" name="log_week[@{{ log_index }}]" placeholder="Log week">
				<select name="log_day[@{{ log_index }}]" v-model="log.log_day">
					<option value="1">Monday</option>
					<option value="2">Tuesday</option>
					<option value="3">Wednesday</option>
					<option value="4">Thursday</option>
					<option value="5">Friday</option>
					<option value="6">Saturday</option>
					<option value="7">Sunday</option>
				</select>
				<div>Exercises:</div>
				<div v-for="(exercise_index, exercise) in log.exercise_data" class="exercise">
					<input type="text" value="@{{ exercise.exercise_name }}" name="exercise_name[@{{ log_index }}][@{{ exercise_index }}]" placeholder="Exercise name"><button type="button" v-on:click="deleteExercise(exercise_index, log_index)">x</button>
					<div v-for="(item_index, item) in exercise.item_data" class="logItem">
						<input type="text" value="@{{ item.value }}" name="item_value[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]"> + <input type="text" value="@{{ item.value }}" name="item_plus[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]">
						 x <input type="text" value="@{{ item.reps }}" name="item_reps[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]"> x <input type="text" value="@{{ item.sets }}" name="item_sets[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]">
						@<input type="text" value="@{{ item.rpe }}" name="item_rpe[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]">
						<input type="text" value="@{{ item.comment }}" name="item_comment[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" placeholder="Comment">
						<select name="item_type[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" v-model="item.type">
							<option value="W">Weight</option>
							<option value="RM">Rep Max</option>
							<option value="P">Percent</option>
							<option value="D">Distance</option>
							<option value="T">Time</option>
						</select>
						<button type="button" v-on:click="deleteItem(item_index, exercise_index, log_index)">x</button>
					</div>
					<button type="button" v-on:click="addItem(exercise_index, log_index)">Add Item</button>
				</div>
				<button type="button" v-on:click="addExercise(log_index)">Add Exercise</button>
			</div>
		</template>
		<button type="button" v-on:click="addLog">Add log</button>
	</div>
	{!! csrf_field() !!}
	<button type="submit" class="btn btn-default">Submit</button>
</form>
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/vue/1.0.26/vue.min.js"></script>
<script>
var default_item = {
	value: 0,
	reps: 0,
	sets: 0,
	rpe: 0,
	comment: '',
	type: 'W'
}

var deafult_exercise = {
	exercise_name: '',
	item_data: [Object.assign({}, default_item)]
};

new Vue({
  el: '#app',
  data: {
    log_data: [
    	{
		log_name: 'woop',
		log_week: 1,
		log_day: 1,
        exercise_data: [
        	{
        	exercise_name: 'Jam',
            item_data: [
            	{
					value: 50,
					reps: 5,
					sets: 5,
					rpe: 0,
					comment: '',
					type: 'T'
				}
            ]
          }
        ]
      },
      {
      	log_name: 'poop',
		log_week: 1,
        log_day: 2,
        exercise_data: [
        	{
			exercise_name: 'Ham',
            item_data: [
            	{
					value: 50,
					reps: 5,
					sets: 5,
					rpe: 0,
					comment: '',
					type: 'W'
				}
            ]
          }
        ]
      },
    ]
  },
  methods: {
    addLog: function(){
		this.log_data.push({log_name: '', log_week:1, log_day:1, exercise_data: Object.assign({}, deafult_exercise)});
    },
    addExercise: function(index_id) {
    	this.log_data[index_id].exercise_data.push(Object.assign({}, deafult_exercise));
    },
    addItem: function(index_id, log_id) {
    	this.log_data[log_id].exercise_data[index_id].item_data.push(Object.assign({}, default_item));
    },
	deleteLog: function(log_index) {
    	this.log_data.splice(log_index, 1);
    },
	deleteExercise: function(exercise_index, log_index) {
    	this.log_data[log_index].exercise_data.splice(exercise_index, 1);
    },
	deleteItem: function(item_index, exercise_index, log_index) {
		this.log_data[log_index].exercise_data[exercise_index].item_data.splice(item_index, 1);
    }
  }
});
</script>
@endsection
