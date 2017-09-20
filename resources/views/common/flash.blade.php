@if (Session::has('flash_message'))
	<div class="alert alert-{{ (Session::has('flash_message_type') ? session('flash_message_type') : 'success' ) }} {{ (Session::has('flash_message_important') ? 'alert-important alert-dismissible fade in' : '' ) }}">
		@if (Session::has('flash_message_important'))
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		@endif

		{!! session('flash_message') !!}
	</div>
@endif
