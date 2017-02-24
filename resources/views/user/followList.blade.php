@extends('layouts.master')

@section('title', 'Who do I follow')

@section('headerstyle')
@endsection

@section('content')
<div class="narrow-centre-block">
  <h2>Following</h2>
  <table class="table table-striped">
    <tbody>
@foreach ($followed_users as $followed_user)
        <td>{{ $followed_user->user->user_name }}</td>
        <td>{{ $followed_user->created_at->toDateString() }}</td>
        <td><p class="btn btn-default"><a href="{{ route('unfollowUser', ['user' => $followed_user->user->user_name]) }}">Unfollow <img src="{{ asset('img/user_delete.png') }}"></a></p></td>
      </tr>
@endforeach
    </tbody>
  </table>

  {!! $followed_users->render() !!}
</div>
@endsection

@section('endjs')
@endsection
