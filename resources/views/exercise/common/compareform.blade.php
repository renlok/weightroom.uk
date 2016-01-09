<form action="{{ url('exercise/compare') }}" method="post">
<div class="form-group">
    <label for="ex">Exercises to compare <small>max 5</small></label>
    <p><small class="text-muted"><i>(To select multiple exercises hold Ctrl or Cmd)</i></small></p>
    <select name="exercises[]" size="10" multiple required id="exercises" class="form-control">
    @foreach ($exercises as $exercise)
        <option value="{{ $exercise->exercise_name }}" {{ in_array($exercise->exercise_name, old('exercises')) ? 'selected' : '' }}>{{ $exercise->exercise_name }}</option>
    @endforeach
    </select>
</div>
<div class="form-group">
    <label for="reps">Reps</label>
    <select name="reps" required class="form-control">
        <option value="0" {{ (0 == old('reps')) ? 'selected' : '' }}>Estimated 1RM</option>
    @for ($i = 1; $i <= 10; $i++)
        <option value="{{ $i }}" {{ ($i == old('reps')) ? 'selected' : '' }}>{{ $i }}</option>
    @endfor
    </select>
</div>
{!! csrf_field() !!}
<input type="submit" name="action" value="Compare" class="btn btn-default">
</form>
