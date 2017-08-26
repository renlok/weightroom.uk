@extends('layouts.master')

@section('title', 'Who is following me')

@section('headerstyle')
@endsection

@section('content')
    <div class="narrow-centre-block">
        <h2>Followers</h2>
        <table class="table table-striped">
            <tbody>
            @foreach ($followed_users as $followed_user)
                <tr>
                    <td><a href="{{ route('viewUser', ['user_name' => $followed_user->user_name]) }}">{{ $followed_user->user_name }}</a></td>
                    <td>{{ Carbon::parse($followed_user->created_at)->toDateString() }}</td>
                @if (!$followed_user->is_accepted)
                    <td><p class="btn btn-success"><a href="{{ route('acceptUserFollow', ['user' => $followed_user->user_name]) }}">Accept <img src="{{ asset('img/user_add.png') }}"></a></p></td>
                @elseif ($followed_user->is_following)
                    <td><p class="btn btn-default"><a href="{{ route('unfollowUser', ['user' => $followed_user->user_name]) }}">Unfollow <img src="{{ asset('img/user_delete.png') }}"></a></p></td>
                @else
                    <td><p class="btn btn-default"><a href="{{ route('followUser', ['user' => $followed_user->user_name]) }}">Follow <img src="{{ asset('img/user_add.png') }}"></a></p></td>
                @endif
                </tr>
            @endforeach
            </tbody>
        </table>

        {!! $paginator->render() !!}
    </div>
@endsection

@section('endjs')
@endsection
