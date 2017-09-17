@extends('layouts.master')

@section('title', 'Admin: View Logs')

@section('headerstyle')
    <style>
    .log-data {
        width: 100%;
        max-height: 500px;
        min-height: 100px;
        overflow-y:scroll;
    }
    </style>
@endsection

@section('content')
    <h2>Admin Land: View Logs</h2>
    <p><a href="{{ route('adminHome') }}">Admin Home</a></p>

    <form class="form-inline margintb">
        <div class="form-group">
            <select class="form-control" id="log-name">
                @foreach ($files as $file)
                <option>{{ $file }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-default" id="view-log">View log</button>
        <a href="{{ route('adminViewLogs', ['raw' => 1, 'log' => $log]) }}" class="btn btn-danger">View Raw</a>
        <a href="{{ route('adminDeleteLog', ['log' => $log]) }}" class="btn btn-danger">Clear log</a>
    </form>
    <div class="log-data">{{ $raw ? $log_contents : (new \Illuminate\Support\Debug\Dumper)->dump($log_contents) }}</div>
@endsection

@section('endjs')
    <script>
        $("#view-log").click(function(){
            var url = '{{ route("adminViewLogs", ['raw' => 0, "log" => ":log"]) }}';
            window.location.href = url.replace(':log', $("#log-name").val());
            return false;
        });
    </script>
@endsection
