@extends('layouts.master')

@section('title', 'Import CSV file')

@section('headerstyle')
@endsection

@section('content')
@include('errors.validation')
<form action="{{ url('import') }}" method="post" enctype="multipart/form-data">
  <div class="form-group">
    <label for="csvfile">File input</label>
    <input type="file" name="csvfile" id="csvfile">
    <p class="help-block">Example block-level help text here.</p>
  </div>
  {{ csrf_field() }}
  <button type="submit" class="btn btn-default">Submit</button>
</form>
@endsection

@section('endjs')
@endsection
