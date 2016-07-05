<select name="exercise[{{ $dropownName }}]" class="form-control">
	<option value="0">Select Exercise</option>
@foreach ($exercises as $exercise)
	<option value="{{ $exercise->exercise_id }}"{{ ($exercise->exercise_name == $selected) ? ' selected="selected"' : '' }}>{{ $exercise->exercise_name }}</option>
@endforeach
</select>
