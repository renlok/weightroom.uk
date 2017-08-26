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
      <tr>
        <td><a href="{{ route('viewUser', ['user_name' => $followed_user->user->user_name]) }}">{{ $followed_user->user->user_name }}</a></td>
        <td>{{ $followed_user->created_at->toDateString() }}</td>
      @if ($followed_user->is_accepted)
        <td><p class="btn btn-default"><a href="{{ route('unfollowUser', ['user' => $followed_user->user->user_name]) }}">Unfollow <img src="{{ asset('img/user_delete.png') }}"></a></p></td>
      @else
        <td><p class="btn btn-default btn-gray"><a href="{{ route('unfollowUser', ['user' => $followed_user->user->user_name]) }}">Cancel request <img src="{{ asset('img/user_delete.png') }}"></a></p></td>
      @endif
      </tr>
@endforeach
    </tbody>
  </table>

  {!! $followed_users->render() !!}
</div>
@endsection

@section('endjs')
@endsection
