@extends('layouts.master')

@section('title', 'Exercise Groups')

@section('headerstyle')
<link href="{{ asset('css/tag-basic-style.css') }}" rel="stylesheet">
@endsection

@section('content')
<h2>Exercise Groups</h2>
<form class="form-inline" action="{{ route('addExerciseGroup') }}" method="post">
  <div class="form-group">
    <label for="newgroup">New Group</label>
    <input type="text" class="form-control" id="newgroup" name="newgroup">
  </div>
  <button type="submit" class="btn btn-default">Add Group</button>
</form>
@foreach ($groups as $group)
  <h3>{{ $group->exgroup_name }} <a href="{{ route('deleteExerciseGroup', ['group_id' => $group->exgroup_id]) }}" class="btn btn-default" role="button">x</a></h3>
  <div data-tags-input-name="tag" id="group-{{ $group->exgroup_id }}">
  @foreach ($group->exercise_group_relations as $exercises)
    {{ $exercises->exercise->exercise_name }}
  @endforeach
  </div>
  <script>
  $(document).ready(function() {
      var t = $("#group-{{ $group->exgroup_id }}").tagging();
      // Execute callback when a tag is added
      $tag_box.on( "add:after", function ( el, text, tagging ) {
          var url = '{{ route('addToExerciseGroup', ['group_id' => $group->exgroup_id, 'exercise_name' => ':name']) }}';
          $.ajax({
              url: url.replace(':name', text),
              type: 'GET',
              dataType: 'json',
              cache: true
          }).done(function(o) {}).fail(function() {}).always(function() {});
      });

      // Execute callback when a tag is removed
      $tag_box.on( "remove:after", function ( el, text, tagging ) {
          var url = '{{ route('getDeleteFromGroup', ['group_id' => $group->exgroup_id, 'exercise_name' => ':name']) }}';
          $.ajax({
              url: url.replace(':name', text),
              type: 'GET',
              dataType: 'json',
              cache: true
          }).done(function(o) {}).fail(function() {}).always(function() {});
      });
  });
  </script>
@endforeach
@endsection

@section('endjs')
<script src="{{ asset('js/tagging.min.js') }}"></script>
@endsection
