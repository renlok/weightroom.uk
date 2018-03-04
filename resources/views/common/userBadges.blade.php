@if ($user->user_gender == 'f')
<img src="{{ asset('img/female.png') }}" alt="Woman">
@endif
@if ($user->user_gender == 'm')
<img src="{{ asset('img/male.png') }}" alt="Man">
@endif
@if ($user->user_beta == 1)
<img src="{{ asset('img/bug.png') }}" alt="Beta tester">
@endif
@if ($user->user_shadowban == 1)
<img src="{{ asset('img/sound_mute.png') }}" alt="Shadow Banned">
@endif
@if ($user->user_admin == 1)
<img src="{{ asset('img/star.png') }}" alt="Adminnosaurus Rex">
@endif
