@extends('layouts.master')

@section('title', 'Export Workout Data')

@section('headerstyle')
<link href="{{ asset('css/pickmeup.css') }}" rel="stylesheet">
@endsection

@section('content')
<h2>Export Workout Data</h2>
@include('common.beta')
@include('common.flash')
@include('errors.validation')
<form action="{{ route('processExport') }}" method="post">
  <div class="form-inline padding">
    <div class="form-group">
      <label for="from_date">From</label>
      <input type="text" class="form-control" id="from_date" name="from_date" value="{{ $from_date }}">
    </div>
    <div class="form-group">
      <label for="to_date">Until</label>
      <input type="text" class="form-control" id="to_date" name="to_date" value="{{ $to_date }}">
    </div>
  </div>
  <div class="form-inline padding">
    <div class="form-group">
      <label for="format">Format</label>
      <select class="form-control" name="format" id="format">
        <option value="csv">CSV File</option>
      </select>
    </div>
  </div>
  {{ csrf_field() }}
  <button type="submit" class="btn btn-default">Export</button>
</form>
@endsection

@section('endjs')
<script src="{{ asset('js/jquery.pickmeup.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.3/moment.min.js" charset="utf-8"></script>
<script>
    $('#from_date').pickmeup({
        date           : moment('{{ $from_date }}','YYYY-MM-DD').format(),
        format         : 'Y-m-d',
        calendars      : 1,
        first_day      : {{ Auth::user()->user_weekstart }},
        hide_on_select : true
    });
    $('#to_date').pickmeup({
        date           : moment('{{ $to_date }}','YYYY-MM-DD').format(),
        format         : 'Y-m-d',
        calendars      : 1,
        first_day      : {{ Auth::user()->user_weekstart }},
        hide_on_select : true
    });
</script>
@endsection
