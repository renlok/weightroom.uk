<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// define the file dirs
define('MAINDIR', dirname(__FILE__) . '/');
define('INCDIR', MAINDIR . 'inc/');
define('PAGEDIR', MAINDIR . 'pages/');
require INCDIR . 'functions_global.php';

// set the database
require INCDIR . 'class_db_handle.php';
$db = new db_handle();
// join the db party
$DbHost = 'localhost';
$DbUser = 'welinkco_tracker';
$DbPassword = 'oP4NBB^V_0qu';
$DbDatabase = 'welinkco_tracker';
$db->connect($DbHost, $DbUser, $DbPassword, $DbDatabase);

// load template handler
require INCDIR . 'template.php';
$template = new template();
$template->set_template();

// load user handler
require INCDIR . 'class_user.php';
$user = new user();

// temp crappy layout, need to add templates
$page = (isset($_GET['page'])) ? $_GET['page'] : '';
switch ($page)
{
	case 'login':
		include PAGEDIR . 'login.php';
		break;
	case 'register':
		include PAGEDIR . 'register.php';
		break;
	case 'log':
		include PAGEDIR . 'log.php';
		break;
	case 'exercise':
		include PAGEDIR . 'exercise.php';
		break;
	default:
}
?>
<p><a href="?page=login">login</a></p>
<p><a href="?page=register">register</a></p>
<p><a href="?page=log">log</a></p>
