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
  <div>
    <label for="templateDesc">Template description</label>
    <textarea type="text" id="templateDesc" name="template_description">{{ $template_description }}</textarea>
    <label for="templateName">Template type</label>
    <select name="template_type">
      <option value="powerlifting" {{ ($template_type == 'powerlifting') ? 'selected="selected"' : '' }}>powerlifting</option>
      <option value="running" {{ ($template_type == 'running') ? 'selected="selected"' : '' }}>running</option>
      <option value="weightlifting" {{ ($template_type == 'weightlifting') ? 'selected="selected"' : '' }}>weightlifting</option>
      <option value="crossfit" {{ ($template_type == 'crossfit') ? 'selected="selected"' : '' }}>crossfit</option>
      <option value="bodybuilding" {{ ($template_type == 'bodybuilding') ? 'selected="selected"' : '' }}>bodybuilding</option>
    </select>
  </div>
  <div id="app">
    <template v-for="(log, log_index) in log_data">
      <div class="log" style="@{{ ((log_index % 2) == 1 ? 'background-color: #ddd;' : '') }}">
        <label>Log</label>
        <input type="text" value="@{{ log.log_name }}" name="log_name[@{{ log_index }}]" placeholder="Log name"><button type="button" v-on:click="deleteLog(log_index)">x</button>
        <div>
          <label>Week #:</label>
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
        </div>
        <div>Exercises:</div>
        <div v-for="(exercise, exercise_index) in log.exercise_data" class="exercise">
          <input type="text" value="@{{ exercise.exercise_name }}" name="exercise_name[@{{ log_index }}][@{{ exercise_index }}]" placeholder="Exercise name"><button type="button" v-on:click="deleteExercise(exercise_index, log_index)">x</button>
          <div v-for="(item, item_index) in exercise.item_data" class="logItem">
            <input type="text" value="@{{ item.value }}" name="item_value[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" class="inputNumber"> + <input type="text" value="@{{ item.plus }}" name="item_plus[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" class="inputNumber">
             x <input type="text" value="@{{ item.reps }}" name="item_reps[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" class="inputNumber"> x <input type="text" value="@{{ item.sets }}" name="item_sets[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" class="inputNumber">
            @<input type="text" value="@{{ item.rpe }}" name="item_rpe[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" class="inputNumber">
            <input type="text" value="@{{ item.comment }}" name="item_comment[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" placeholder="Comment">
            <select name="item_type[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" v-model="exercise.item_data[item_index].type">
              <option value="W">Weight</option>
              <option value="RM">Rep Max</option>
              <option value="P">Percent</option>
              <option value="D">Distance</option>
              <option value="T">Time</option>
            </select>
            <label>warmup?</label>
            <input type="checkbox" value="1" name="item_warmup[@{{ log_index }}][@{{ exercise_index }}][@{{ item_index }}]" v-model="item.warmup">
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

@if ($template_id > 0)
<button type="button" class="btn btn-danger deleteLink">Delete Template</button>
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

var deafult_exercise = {
    exercise_name: '',
    item_data: [Object.assign({}, default_item)]
};

new Vue({
    el: '#app',
    data: {
        log_data: {!! $json_data !!}
    },
    methods: {
        addLog: function(){
            this.log_data.push({log_name: '', log_week:1, log_day:1, exercise_data: [Object.assign({}, deafult_exercise)]});
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
