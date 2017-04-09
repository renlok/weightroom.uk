@extends('layouts.master')

@section('title', 'Import CSV file')

@section('headerstyle')
@endsection

@section('content')
@if ($map_match != '')
<p>CSV from: {{ $map_match }}</p>
@endif
@include('common.flash')
<form action="{{ route('storeImport') }}" method="post">
<table class="table table-striped">
  <thead>
    <tr>
      <th>Column Name</th>
      <th>Link to</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
@foreach ($first_row as $file_header => $first_data)
    <tr>
      <td>
        <label for="{{ $file_header }}">{{ $file_header }}</label>
      </td>
      <td>
    	  <select class="form-control" name="{{ $file_header }}">
    	    <option value="">Ignore</option>
	@foreach ($column_names as $column_name => $column_desc)
          <option value="{{ $column_name }}" {{ ($link_array[$file_header] == $column_name) ? 'selected="selected"' : '' }}>{{ $column_desc }}</option>
	@endforeach
        </select>
      </td>
      <td>
        {{ $first_data }}
      </td>
    </tr>
@endforeach
  </tbody>
</table>
  {{ csrf_field() }}
  <button type="submit" class="btn btn-default">Submit</button>
</form>
@endsection

@section('endjs')
@endsection
