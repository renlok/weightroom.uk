@extends('layouts.master')

@section('title', 'List Exercises')

@section('content')
	<h2>Exercise List</h2>
<p>This is a list of every exercise you have logged so far.</p>
<ul class="list-group">
@forelse ($exercises as $exercise)
	<li class="list-group-item">
		<span class="badge">{{ $exercise->COUNT }}</span>
		<a href="{{ route('viewExercise', ['exercise_name' => rawurlencode($exercise->exercise_name_clean)]) }}">{{ $exercise->exercise_name }}</a>
	</li>
@empty
	<li class="list-group-item">
		You have not added any exercises yet, why not get started and <a href="{{ route('newLog', ['date' => Carbon\Carbon::now()->format('Y-m-d')]) }}">track your first workout</a>.
	</li>
@endforelse
</ul>

{!! $exercises->render() !!}
@endsection
