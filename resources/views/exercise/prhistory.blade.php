@extends('layouts.master')

@section('title', 'PR History: ' . $exercise_name)

@section('headerstyle')
@endsection

@section('content')
<h2>PR History: {{ $exercise_name }}</h2>
<p><small><a href="{{ route('viewExercise', ['exercise_name' => $exercise_name]) }}">&larr; Back to exercise</a></small> | <small><a href="{{ route('exerciseHistory', ['exercise_name' => $exercise_name]) }}">View history</a></small></p>
<table class="table table-striped">
	<thead>
		<th>Date</th>
		<th>1 RM</th>
		<th>2 RM</th>
		<th>3 RM</th>
		<th>4 RM</th>
		<th>5 RM</th>
		<th>6 RM</th>
		<th>7 RM</th>
		<th>8 RM</th>
		<th>9 RM</th>
		<th>10 RM</th>
	</thead>
	<tbody>
@foreach ($prs as $date => $pr)
		<tr>
			<td>{{ $date }}</td>
		@for ($i = 1; $i <= 10; $i++)
			<td>{{ (isset($pr[$i])) ? Format::correct_weight($pr[$i]) . ' ' . Auth::user()->user_unit : '' }}</td>
		@endfor
		</tr>
@endforeach
	</tbody>
</table>
@endsection

@section('endjs')
@endsection
