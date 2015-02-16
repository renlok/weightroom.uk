<?php
// define the file dirs
define('MAINDIR', dirname(__FILE__) . '/');
define('INCDIR', MAINDIR . '/inc');
define('PAGEDIR', MAINDIR . '/pages');

// set the database
require INCDIR . 'class_db_handle.php';
$db = new db_handle();
// join the db party
$db->connect($DbHost, $DbUser, $DbPassword, $DbDatabase);

// temp crappy layout, need to add templates
switch ($_GET['do'])
{
	case 'login':
		include PAGEDIR . 'login.php';
		break;
	case 'login_do':
		include PAGEDIR . 'login_do.php';
		break;
	case 'register':
		include PAGEDIR . 'register.php';
		break;
	case 'calendar':
		include PAGEDIR . 'calendar.php';
		break;
	case 'add_log':
		include PAGEDIR . 'add_log.php';
		break;
	case 'view_exercise':
		include PAGEDIR . 'view_exercise.php';
		break;
}
?>