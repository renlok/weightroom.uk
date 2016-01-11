@if (Session::has('flash_message'))
<!-- Form Error Box -->
<div class="alert alert-danger">
    <strong>{{ session('error') }}</strong>
</div>
@endif
