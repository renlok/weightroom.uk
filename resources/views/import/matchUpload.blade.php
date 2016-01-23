@extends('layouts.master')

@section('title', 'Import CSV file')

@section('headerstyle')
@endsection

@section('content')
<form action="{{ url('import') }}" method="post">
@foreach ($headers as $header)
  <div class="form-group">
    <label for="{{ $header }}">{{ $header }}</label>
	<select class="form-control" name="{{ $header }}">
	  <option value="">Ignore</option>
	@foreach ($colomn_names as $colomn_name)
	  <option value="{{ $colomn_name }}">{{ $colomn_name }}</option>
	@endforeach
	</select>
  </div>
@endforeach
  {{ csrf_field() }}
  <button type="submit" class="btn btn-default">Submit</button>
</form>
@endsection

@section('endjs')
@endsection
