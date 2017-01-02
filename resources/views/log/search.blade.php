@extends('layouts.master')

@section('title', 'Search Logs')

@section('headerstyle')
<style>
.labeldd {
	padding-top: 0px !important;
}
</style>
@endsection

@section('content')
<h2>Search logs</h2>
@include('errors.validation')
<p>Find logs that meet the following criteria:</p>

<form class="form-horizontal" action="{{ url('log/search') }}" method="post">
  <div class="form-group">
    <label for="show" class="col-sm-2 control-label">Show</label>
    <div class="col-sm-10">
    <select class="form-control" name="show" id="show">
	  <option value="1"{{ (old('show') == 1) ? ' selected="selected"' : ''}}>the last log</option>
	  <option value="5"{{ (old('show') == 5) ? ' selected="selected"' : ''}}>the last five logs</option>
	  <option value="10"{{ (old('show') == 10) ? ' selected="selected"' : ''}}>the last ten logs</option>
	  <option value="0"{{ (old('show') == 0) ? ' selected="selected"' : ''}}>every log</option>
	</select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-10 col-md-offset-2">
      <p class="form-control-static">which meet these criteria</p>
    </div>
  </div>
  <div class="form-group">
    <label for="exercise" class="col-sm-2 control-label">Exercise</label>
    <div class="col-sm-10">
    <select class="form-control" name="exercise" id="exercise">
    @foreach ($exercises as $exercise)
        <option value="{{ $exercise->exercise_name }}"{{ ($exercise->exercise_name == old('exercise')) ? ' selected="selected"' : '' }}>{{ $exercise->exercise_name }}</option>
    @endforeach
	</select>
    </div>
  </div>
  <div class="form-group" id="type_changer">
    <label for="weight" class="col-sm-2 control-label labeldd">
		<select class="form-control" name="valuetype" id="valuetype" v-on:change="change_type" v-model="selected">
            <option value="weight"{{ ('weight' == old('valuetype')) ? ' selected="selected"' : '' }}>Weight</option>
            <option value="distance"{{ ('distance' == old('valuetype')) ? ' selected="selected"' : '' }}>Distacne</option>
            <option value="time"{{ ('time' == old('valuetype')) ? ' selected="selected"' : '' }}>Time</option>
        </select>
    </label>
	<label for="weight" class="col-sm-1 control-label labeldd">
        <select class="form-control" name="valueunit" id="valueunit">
            <option value="@{{ unit }}"{{ ('weight_kg' == old('valueunit')) ? ' selected="selected"' : '' }} v-for="unit in units">@{{ unit }}</option>
        </select>
    </label>
    <div class="col-sm-9">
        <div class="row">
            <div class="col-md-2">
                <div class="input-group">
                    <select class="form-control" name="weightoperator" id="weightoperator">
                        <option value="="{{ ('=' == old('weightoperator')) ? ' selected="selected"' : '' }}>=</option>
                        <option value=">="{{ ('>=' == old('weightoperator')) ? ' selected="selected"' : '' }}>&gt;=</option>
                        <option value="<="{{ ('<=' == old('weightoperator')) ? ' selected="selected"' : '' }}>&lt;=</option>
                        <option value=">"{{ ('>' == old('weightoperator')) ? ' selected="selected"' : '' }}>&gt;</option>
                        <option value="<"{{ ('<' == old('weightoperator')) ? ' selected="selected"' : '' }}>&lt;</option>
                    </select>
                </div>
            </div>
            <div class="col-md-10">
                <div class="input-group">
                    <input type="text" class="form-control" name="weight" id="weight" placeholder="@{{ type_placeholder }}" value="{{ old('weight') }}">
                    <div class="input-group-addon" v-model="type_unit">@{{ type_unit }}</div>
                </div>
            </div>
        </div>
	</div>
  </div>
  <div class="form-group">
    <label for="reps" class="col-sm-2 control-label">Reps</label>
    <div class="col-sm-10">
    <input type="text" class="form-control" name="reps" id="reps" placeholder="any or a number" value="{{ old('reps') }}">
    </div>
  </div>
  <div class="form-group">
    <label for="orderby" class="col-sm-2 control-label">Order by</label>
    <div class="col-sm-10">
        <select class="form-control" name="orderby" id="orderby">
          <option value="asc"{{ ('asc' == old('orderby')) ? ' selected="selected"' : '' }}>Oldest first</option>
          <option value="desc"{{ ('desc' == old('orderby')) ? ' selected="selected"' : '' }}>Newest first</option>
        </select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      {!! csrf_field() !!}
      <button type="submit" class="btn btn-default">Search</button>
    </div>
  </div>
</form>

@if (count($log_exercises) > 0)
    @if (old('show') > 0)
        <h3>Showing {{ old('show') }} matching logs</h3>
    @else
		@if (count($log_exercises) == 50)
			<h3>Search returned over 50 results <small>Showing only the first 50 results</small></h3>
		@else
			<h3>Search returned {{ count($log_exercises) }} results</h3>
		@endif
    @endif
@endif

@foreach ($log_exercises as $log_exercise)
    @include('common.logExercise', ['view_type' => 'search'])
@endforeach
@endsection

@section('endjs')
<script src="//cdnjs.cloudflare.com/ajax/libs/vue/1.0.28/vue.min.js" charset="utf-8"></script>
<script>
new Vue({
    el: '#type_changer',
    data: {
        selected: '{{ old('valuetype', 'weight') }}',
        type_unit: '{{ $user->user_unit }}',
        type_placeholder: 'Weight',
@if (old('valuetype', 'weight') == 'weight')
		units: {!! (Auth::user()->user_unit == 'lb') ? "['lb', 'kg']" : "['kg', 'lb']" !!}
@elseif  (old('valuetype', 'weight') == 'distance')
		units: ['km', 'm', 'mile']
@else
		units: ['h', 'm', 's']
@endif
    },
    methods: {
        change_type: function () {
            if (this.selected == 'weight')
            {
                this.type_unit = '{{ $user->user_unit }}';
                this.type_placeholder = 'Weight';
				this.units = {!! (Auth::user()->user_unit == 'lb') ? "['lb', 'kg']" : "['kg', 'lb']" !!};
            }
            else if (this.selected == 'distance')
            {
                this.type_unit = 'km';
                this.type_placeholder = 'Distance';
				this.units = ['km', 'm', 'mile'];
            }
            else
            {
                this.type_unit = 'hr';
                this.type_placeholder = 'Time';
				this.units = ['h', 'm', 's'];
            }
        }
    }
});
</script>
@endsection
