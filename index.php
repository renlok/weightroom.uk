<?php
// temp crappy layout, need to add templates
switch ($_GET['do'])
{
	case 'login':
		include 'login.php';
		break;
	case 'login_do':
		include 'login_do.php';
		break;
	case 'register':
		include 'register.php';
		break;
	case 'calendar':
		include 'calendar.php';
		break;
	case 'add_log':
		include 'add_log.php';
		break;
	case 'view_exercise':
		include 'view_exercise.php';
		break;
}
?>