@extends('layouts.master')

@section('title', 'User Search')

@section('content')
<h2>User search</h2>
<form class="form-inline" method="post" action="{{ route('userSearch') }}">
  <div class="form-group">
	<label class="sr-only">Username</label>
  </div>
  <div class="form-group">
	<label for="Username2" class="sr-only">Username</label>
	<input type="text" class="form-control" id="Username2" placeholder="Username" name="username" value="{{ old('username') }}">
  </div>
  {{ csrf_field() }}
  <button type="submit" class="btn btn-default">Search</button>
</form>

<h3>Results</h3>
<table class="table">
<tbody>
@forelse ($users as $user_name)
	<tr>
		<td class="logrow">
			<a href="{{ route('viewUser', ['user_name' => $user_name]) }}">{{ $user_name }}</a>
		</td>
	</tr>
@empty
	<tr>
		<td class="logrow">
			Your search returned no users
		</td>
	</tr>
@endforelse
</tbody>
</table>
@endsection
