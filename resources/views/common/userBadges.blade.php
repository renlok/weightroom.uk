@if ($user->user_gender == 'f')
<img src="img/female.png" alt="Woman">
@endif
@if ($user->user_gender == 'm')
<img src="img/male.png" alt="Man">
@endif
@if ($user->user_beta == 1)
<img src="img/bug.png" alt="Beta tester">
@endif
@if ($user->user_admin == 1)
<img src="img/star.png" alt="Adminnosaurus Rex">
@endif
