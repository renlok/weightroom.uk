@if (Session::has('flash_message'))
	<div class="alert alert-{{ (Session::has('flash_message_type') ? session('flash_message_type') : 'success' ) }} {{ (Session::has('flash_message_important') ? 'alert-important' : '' ) }}">
		@if (Session::has('flash_message_important'))
			<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		@endif

		{{ session('flash_message') }}
	</div>
@endif
