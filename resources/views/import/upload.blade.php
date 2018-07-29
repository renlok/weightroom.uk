@extends('layouts.master')

@section('title', 'Import CSV file')

@section('headerstyle')
@endsection

@section('content')
<h2>Import Workouts</h2>
<p>Import workouts via <a href="https://en.wikipedia.org/wiki/Comma-separated_values">csv files</a>.</p>
<p>The import process make take a while depending on the size of the file you upload, just be patient.</p>
<p>For the import process to run smoothly and for no data to be lost the file must meet the following requirements:</p>
<ul>
  <li>The file row must be a header row.</li>
  <li>Commas (<b>,</b>) must be used as a delimiter, semi-colons (<b>;</b>) are not accepted.</li>
  <li>Cell data can be contained within quotation marks (<b>"</b>) but this is not required.</li>
</ul>
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
    <input type="file" name="csvfile" id="csvfile" class="form-control">
  </div>
  {{ csrf_field() }}
  <button type="submit" class="btn btn-default">Upload</button>
</form>
@endif
@endsection

@section('endjs')
@endsection
