@extends('layouts.master')

@section('title', 'Import CSV file')

@section('headerstyle')
@endsection

@section('content')
<form action="{{ url('import') }}" method="post">
<table class="table table-striped">
  <thead>
    <tr>
      <th>Colomn Name</th>
      <th>Link to</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
@foreach ($file_headers as $file_header)
    <tr>
      <td>
        <label for="{{ $file_header }}">{{ $file_header }}</label>
      </td>
      <td>
    	  <select class="form-control" name="{{ $file_header }}">
    	    <option value="">Ignore</option>
	@foreach ($colomn_names as $colomn_name => $colomn_desc)
          <option value="{{ $colomn_name }}">{{ $colomn_desc }}</option>
	@endforeach
        </select>
      </td>
      <td>
        {{ $first_row->$file_header }}
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
