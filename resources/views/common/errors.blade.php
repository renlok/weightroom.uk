@if (count($errors) > 0 || count($error) > 0)
    <!-- Form Error List -->
    <div class="alert alert-danger">
        <strong>Whoops! Something went wrong!</strong>

        <ul>
            @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            @endif
            @if (count($error) > 0)
                <li>{{ $error }}</li>
            @endif
        </ul>
    </div>
@endif
