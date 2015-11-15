<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
$charset = 'UTF-8';

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

// Atuomatically login user is necessary "Remember me" option
if (!$user->logged_in && isset($_COOKIE['TRACKER_RM_ID']))
{
	// remove junk
	$query = "DELETE FROM auth_tokens WHERE token_expires < :token_expires";
	$params = array();
	$params[] = array(':token_expires', date("Y-m-d"), 'str');
	$db->query($query, $params);
	// check you have a remember me token
	$query = "SELECT user_id FROM auth_tokens WHERE token = :RM_ID";
	$params = array();
	$params[] = array(':RM_ID', $_COOKIE['TRACKER_RM_ID'], 'str');
	$db->query($query, $params);
	if ($db->numrows() > 0)
	{
		// generate a random unguessable token
		$_SESSION['csrftoken'] = generateToken();
		$user_id = $db->result('user_id');
		$query = "SELECT user_hash, user_pass FROM users WHERE user_id = :user_id";
		$params = array();
		$params[] = array(':user_id', $user_id, 'int');
		$db->query($query, $params);
		$password = $db->result('user_pass');
		$_SESSION['TRACK_LOGGED_IN'] 		= $user_id;
		$_SESSION['TRACK_LOGGED_NUMBER'] 	= strspn($password, $db->result('user_hash'));
		$_SESSION['TRACK_LOGGED_PASS'] 		= $password;
		header('location: ?page=log');
	}
}

$page = (isset($_GET['page'])) ? $_GET['page'] : '';
$do = (isset($_GET['do'])) ? $_GET['do'] : '';
$template->assign_vars(array(
	'NOT_LOGGED_IN' => !$user->logged_in,
	'WEIGHT_UNIT' => ($user->logged_in && $user->user_data['user_unit'] == 2) ? 'lb' : 'kg',
	'B_IN_TOOLS' => in_tools($page, $do),
	'CURRENT_PAGE' => $page,
	'CURRENT_DO' => $do
	));
load_global_tempalte();

switch ($page)
{
	case 'login':
		include PAGEDIR . 'login.php';
		break;
	case 'register':
		include PAGEDIR . 'register.php';
		break;
	case 'log_beta':
		include PAGEDIR . 'log_beta.php';
		break;
	case 'log':
		include PAGEDIR . 'log.php';
		break;
	case 'bodyweight':
		include PAGEDIR . 'bodyweight.php';
		break;
	case 'wilks':
		include PAGEDIR . 'wilks.php';
		break;
	case 'sinclair':
		include PAGEDIR . 'sinclair.php';
		break;
	case 'invites':
		include PAGEDIR . 'invites.php';
		break;
	case 'logout':
		include PAGEDIR . 'logout.php';
		break;
	case 'exercise':
		include PAGEDIR . 'exercise.php';
		break;
	case 'edit_exercise':
		include PAGEDIR . 'edit_exercise.php';
		break;
	case 'history':
		include PAGEDIR . 'history.php';
		break;
	case 'ajax':
		include PAGEDIR . 'ajax.php';
		break;
	case 'search':
		include PAGEDIR . 'search.php';
		break;
	case 'search_log':
		include PAGEDIR . 'search_log.php';
		break;
	case 'tools':
		include PAGEDIR . 'tools.php';
		break;
	case 'volume':
		include PAGEDIR . 'volume.php';
		break;
	case 'settings':
		include PAGEDIR . 'settings.php';
		break;
	case 'demo':
		include PAGEDIR . 'demo.php';
		break;
	default:
		if ($user->logged_in)
		{
			include PAGEDIR . 'dash.php';
		}
		else
		{
			include PAGEDIR . 'login.php';
		}
}
?>
