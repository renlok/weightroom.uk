@extends('layouts.master')

@section('title', 'Exercise Groups')

@section('headerstyle')
<link href="{{ asset('css/tag-basic-style.css') }}" rel="stylesheet">
@endsection

@section('content')
<h2>Exercise Groups</h2>
@include('common.flash')
<form class="form-inline" action="{{ route('addExerciseGroup') }}" method="post">
  <div class="form-group">
    <label for="newgroup">New Group</label>
    <input type="text" class="form-control" id="newgroup" name="newgroup">
  </div>
  {!! csrf_field() !!}
  <button type="submit" class="btn btn-default">Add Group</button>
</form>
@foreach ($groups as $group)
  <h3>{{ $group->exgroup_name }} <a href="{{ route('deleteExerciseGroup', ['group_id' => $group->exgroup_id]) }}" class="btn btn-default" role="button">x</a></h3>
  <div data-tags-input-name="tag" edit-on-delete="false" no-spacebar="true" id="group-{{ $group->exgroup_id }}">@foreach ($group->exercise_group_relations as $exercises){{ $exercises->exercise->exercise_name }}, @endforeach</div>
  <script>
  $(document).ready(function() {
      var t = $("#group-{{ $group->exgroup_id }}").tagging();
      $tag_box = t[0];
      // Execute callback when a tag is added
      $tag_box.on( "add:after", function ( el, text, tagging ) {
          $.ajax({
              url: '{{ route('addToExerciseGroup') }}',
              type: 'POST',
              data: {'group_name': '{{ $group->exgroup_name }}', 'exercise_name': text.trim(), '_token': '{!! csrf_token() !!}'},
              dataType: 'json',
              cache: true
          }).done(function(o) {}).fail(function() {}).always(function() {});
      });

      // Execute callback when a tag is removed
      $tag_box.on( "remove:after", function ( el, text, tagging ) {
          $.ajax({
              url: '{{ route('deleteFromExerciseGroup') }}',
              type: 'POST',
              data: {'group_name': '{{ $group->exgroup_name }}', 'exercise_name': text.trim(), '_token': '{!! csrf_token() !!}'},
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
