<form action="{{ route('store' . $object_type . 'Comment', ['object_id' => $object_id]) }}" method="post">
    <input type="hidden" name="parent_id" value="{{ $parent_id }}">
    {!! csrf_field() !!}
    <div class="form-group">
        <textarea class="form-control" rows="3" placeholder="Comment" name="comment" maxlength="500"></textarea>
        <p><small>Max. 500 characters</small></p>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-default">Post</button>
    </div>
</form>
