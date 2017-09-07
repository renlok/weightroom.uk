@extends('layouts.master')

@section('title', 'Admin: ' . (($template_id == 0) ? 'Add' : 'Edit') . ' Log Template')

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
.inputNumber {
  width: 30px;
}
.odd {
  background-color: #ddd;
}
</style>
@endsection

@section('content')
<h2>Admin Land: {{ ($template_id == 0) ? 'Add' : 'Edit' }} Log Template</h2>
<p><a href="{{ route('adminHome') }}">Admin Home</a></p>

@include('common.flash')
<form action="{{ ($template_id == 0) ? route('adminAddTemplate') : route('adminEditTemplate', ['template_id' => $template_id]) }}" method="post">
  <input type="hidden" name="template_id" value="{{ $template_id }}">
  <label for="templateName">Template name</label>
  <input type="text" id="templateName" name="template_name" placeholder="Template name" value="{{ $template_name }}">
  <div class="form-group">
    <label for="templateDesc">Template description</label>
    <textarea type="text" id="templateDesc" class="form-control" name="template_description">{{ $template_description }}</textarea>
  </div>
  <div class="form-group">
    <label for="template_type">Template type</label>
    <select name="template_type">
      <option value="powerlifting" {{ ($template_type == 'powerlifting') ? 'selected="selected"' : '' }}>powerlifting</option>
      <option value="running" {{ ($template_type == 'running') ? 'selected="selected"' : '' }}>running</option>
      <option value="weightlifting" {{ ($template_type == 'weightlifting') ? 'selected="selected"' : '' }}>weightlifting</option>
      <option value="crossfit" {{ ($template_type == 'crossfit') ? 'selected="selected"' : '' }}>crossfit</option>
      <option value="bodybuilding" {{ ($template_type == 'bodybuilding') ? 'selected="selected"' : '' }}>bodybuilding</option>
      <option value="general" {{ ($template_type == 'general') ? 'selected="selected"' : '' }}>general</option>
    </select>
  </div>
  <div id="app">
    <template v-for="(log, log_index) in log_data">
      <div class="log" v-bind:class="{'odd': log_index % 2 === 1}">
        <label>Workout</label>
        <input type="text" v-model="log_data[log_index].log_name" v-bind:name="'log_name['+ log_index +']'" placeholder="Workout name">
        <button type="button" v-on:click="moveLog(log_index, 'up')" v-show="log_index > 0">▲</button>
        <button type="button" v-on:click="moveLog(log_index, 'down')" v-show="log_index < log_data.length - 1">▼</button>
        <button type="button" v-on:click="copyLog(log_index)" data-toggle="tooltip" data-placement="top" title="Duplicate workout">c</button>
        <button type="button" v-on:click="deleteLog(log_index)" data-toggle="tooltip" data-placement="top" title="Delete workout">x</button>
        <div>
          <label>Week #:</label>
          <input type="text" v-model="log_data[log_index].log_week" v-bind:name="'log_week['+ log_index +']'" placeholder="Log week">
          <select v-bind:name="'log_day['+ log_index +']'" v-model="log_data[log_index].log_day">
            <option value="1">Monday</option>
            <option value="2">Tuesday</option>
            <option value="3">Wednesday</option>
            <option value="4">Thursday</option>
            <option value="5">Friday</option>
            <option value="6">Saturday</option>
            <option value="7">Sunday</option>
          </select>
        </div>
        <div>Exercises:</div>
        <div v-for="(exercise, exercise_index) in log.exercise_data" class="exercise">
          <input type="text" v-model="log_data[log_index].exercise_data[exercise_index].exercise_name" v-bind:name="'exercise_name['+ log_index +']['+ exercise_index +']'" placeholder="Exercise name">
          <button type="button" v-on:click="moveExercise(exercise_index, log_index, 'up')" v-show="exercise_index > 0">▲</button>
          <button type="button" v-on:click="moveExercise(exercise_index, log_index, 'down')" v-show="exercise_index < log_data[log_index].exercise_data.length - 1">▼</button>
          <button type="button" v-on:click="copyExercise(exercise_index, log_index)" data-toggle="tooltip" data-placement="top" title="Duplicate exercise">c</button>
          <button type="button" v-on:click="deleteExercise(exercise_index, log_index)" data-toggle="tooltip" data-placement="top" title="Delete exercise">x</button>
          <div v-for="(item, item_index) in exercise.item_data" class="logItem">
            <input type="text" v-model="log_data[log_index].exercise_data[exercise_index].item_data[item_index].value" v-bind:name="'item_value['+ log_index +']['+ exercise_index +']['+ item_index +']'" class="inputNumber">
             + <input type="text" v-model="log_data[log_index].exercise_data[exercise_index].item_data[item_index].plus" v-bind:name="'item_plus['+ log_index +']['+ exercise_index +']['+ item_index +']'" class="inputNumber">
             x <input type="text" v-model="log_data[log_index].exercise_data[exercise_index].item_data[item_index].reps" v-bind:name="'item_reps['+ log_index +']['+ exercise_index +']['+ item_index +']'" class="inputNumber">
             x <input type="text" v-model="log_data[log_index].exercise_data[exercise_index].item_data[item_index].sets" v-bind:name="'item_sets['+ log_index +']['+ exercise_index +']['+ item_index +']'" class="inputNumber">
             @ <input type="text" v-model="log_data[log_index].exercise_data[exercise_index].item_data[item_index].rpe" v-bind:name="'item_rpe['+ log_index +']['+ exercise_index +']['+ item_index +']'" class="inputNumber">
            <input type="text" v-model="log_data[log_index].exercise_data[exercise_index].item_data[item_index].comment" v-bind:name="'item_comment['+ log_index +']['+ exercise_index +']['+ item_index +']'" placeholder="Comment">
            <select v-model="log_data[log_index].exercise_data[exercise_index].item_data[item_index].type" v-bind:name="'item_type['+ log_index +']['+ exercise_index +']['+ item_index +']'">
              <option value="W">Weight</option>
              <option value="RM">Rep Max</option>
              <option value="P">Percent</option>
              <option value="D">Distance</option>
              <option value="T">Time</option>
            </select>
            <label>Warmup Set?</label>
            <input type="checkbox" value="1" v-model="log_data[log_index].exercise_data[exercise_index].item_data[item_index].warmup" v-bind:name="'item_warmup['+ log_index +']['+ exercise_index +']['+ item_index +']'">
            <button type="button" v-on:click="moveItem(item_index, exercise_index, log_index, 'up')" v-show="item_index > 0">▲</button>
            <button type="button" v-on:click="moveItem(item_index, exercise_index, log_index, 'down')" v-show="item_index < log_data[log_index].exercise_data[exercise_index].item_data.length - 1">▼</button>
            <button type="button" v-on:click="copyItem(item_index, exercise_index, log_index)" data-toggle="tooltip" data-placement="right" title="Duplicate set">c</button>
            <button type="button" v-on:click="deleteItem(item_index, exercise_index, log_index)" data-toggle="tooltip" data-placement="right" title="Delete set">x</button>
          </div>
          <button type="button" v-on:click="addItem(exercise_index, log_index)">Add Set</button>
        </div>
        <button type="button" v-on:click="addExercise(log_index)">Add Exercise</button>
      </div>
    </template>
    <button type="button" v-on:click="addLog">Add Workout</button>
  </div>
  <div class="form-inline margintb">
    <label for="template_charge">Price ($): </label>
    <input type="input" value="{{ $template_charge }}" name="template_charge" class="form-control">
  </div>
  {!! csrf_field() !!}
  <button type="submit" class="btn btn-default">Submit</button>
@if ($template_id > 0)
  <button type="button" class="btn btn-danger deleteLink">Delete Template</button>
@endif
</form>

@if ($template_id > 0)
<div class="alert alert-danger margintb collapse" role="alert" id="deleteWarning" aria-expanded="false">
  <button type="button" class="close deleteLink"><span aria-hidden="true">&times;</span></button>
  <h4>You sure?</h4>
  <p>You are about to delete this template this cannot be undone</p>
  <p>
    <a href="{{ route('adminDeleteTemplate', ['template_id' => $template_id]) }}" class="btn btn-danger">Yeah delete it</a>
    <button type="button" class="btn btn-default deleteLink">Nah leave it be</button>
  </p>
</div>
@endif
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/vue/2.2.6/vue.min.js"></script>
<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
@if ($template_id > 0)
$('.deleteLink').click(function() {
    $('#deleteWarning').collapse('toggle');
});
@endif

var default_item = {
    value: 0,
    plus: 0,
    reps: 1,
    sets: 1,
    rpe: 0,
    comment: '',
    warmup: 0,
    type: 'W'
}

new Vue({
    el: '#app',
    data: {
        log_data: {!! $json_data !!}
    },
    methods: {
        addLog: function(){
            this.log_data.push({log_name: '', log_week:1, log_day:1, exercise_data: [Object.assign({}, {exercise_name: '', item_data: [Object.assign({}, default_item)]})]});
        },
        addExercise: function(index_id) {
            this.log_data[index_id].exercise_data.push(Object.assign({}, {exercise_name: '', item_data: [Object.assign({}, default_item)]}));
        },
        addItem: function(index_id, log_id) {
            this.log_data[log_id].exercise_data[index_id].item_data.push(Object.assign({}, default_item));
        },
        deleteLog: function(log_id) {
            this.log_data.splice(log_id, 1);
        },
        deleteExercise: function(exercise_id, log_id) {
            this.log_data[log_id].exercise_data.splice(exercise_id, 1);
        },
        deleteItem: function(item_id, exercise_id, log_id) {
            this.log_data[log_id].exercise_data[exercise_id].item_data.splice(item_id, 1);
        },
        copyLog: function(log_id) {
            var new_log = this.log_data[log_id];
            this.log_data.splice(log_id + 1, 0, Object.assign({}, new_log));
        },
        copyExercise: function(exercise_id, log_id) {
            var new_exercise = this.log_data[log_id].exercise_data[exercise_id];
            this.log_data[log_id].exercise_data.splice(exercise_id + 1, 0, Object.assign({}, new_exercise));
        },
        copyItem: function(item_id, exercise_id, log_id) {
            var new_item = this.log_data[log_id].exercise_data[exercise_id].item_data[item_id];
            this.log_data[log_id].exercise_data[exercise_id].item_data.splice(item_id + 1, 0, Object.assign({}, new_item));
        },
        moveLog: function(log_id, dir) {
            if (dir == 'up') {
                if (log_id > 0)
                  this.log_data.move(log_id, log_id - 1);
            } else {
                if (log_id < this.log_data.length - 1)
                    this.log_data.move(log_id, log_id + 1);
            }
        },
        moveExercise: function(exercise_id, log_id, dir) {
            if (dir == 'up') {
                if (exercise_id > 0)
                    this.log_data[log_id].exercise_data.move(exercise_id, exercise_id - 1);
            } else {
                if (exercise_id < this.log_data[log_id].exercise_data.length - 1)
                    this.log_data[log_id].exercise_data.move(exercise_id, exercise_id + 1);
            }
        },
        moveItem: function(item_id, exercise_id, log_id, dir) {
            if (dir == 'up') {
                if (item_id > 0)
                    this.log_data[log_id].exercise_data[exercise_id].item_data.move(item_id, item_id - 1);
            } else {
                if (item_id < this.log_data[log_id].exercise_data[exercise_id].item_data.length - 1)
                    this.log_data[log_id].exercise_data[exercise_id].item_data.move(item_id, item_id + 1);
            }
        },
    }
});

Array.prototype.move = function (old_index, new_index) {
    if (new_index >= this.length) {
        var k = new_index - this.length;
        while ((k--) + 1) {
            this.push(undefined);
        }
    }
    this.splice(new_index, 0, this.splice(old_index, 1)[0]);
    return this; // for testing purposes
};
</script>
@endsection
