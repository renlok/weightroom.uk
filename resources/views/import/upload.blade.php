@extends('layouts.master')

@section('title', 'Import CSV file')

@section('headerstyle')
@endsection

@section('content')
<h2>Import Workouts</h2>
<p>Import workouts via <a href="https://en.wikipedia.org/wiki/Comma-separated_values">csv file</a>. The file needs to be set up with a header row and each set as a new line.</p>
@include('common.beta')
@include('common.flash')
@include('errors.validation')
@if ($imports_remaining > 0)
<div class="alert alert-success">
  You currently have an import being processed, please wait before uploading another file.
  <!-- {{ $imports_remaining }} -->
</div>
@else
<form action="{{ url('import') }}" method="post" enctype="multipart/form-data">
  <div class="form-group">
    <label for="csvfile">File input</label>
    <input type="file" name="csvfile" id="csvfile">
  </div>
  {{ csrf_field() }}
  <button type="submit" class="btn btn-default">Upload</button>
</form>
@endif
@endsection

@section('endjs')
@endsection
