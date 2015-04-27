<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}


require INCDIR . 'class_log.php';
$log = new log();

if(!$log->is_valid_exercise($user->user_id, $_GET['exercise_name']))
{
	print_message('Invalid exercise', '?page=exercise&do=list');
	exit;
}

// rename
if (isset($_POST['username']) && isset($_POST['password']))
{
	if ($user->user_login($_POST['username'], $_POST['password']))
	{
		if (isset($_POST['rememberme']))
		{
			$auth_token = generateToken();
			$query = "INSERT INTO auth_tokens VALUES (NULL, :auth_token, :user_id, :token_expires)";
			$params = array();
			$params[] = array(':auth_token', $auth_token, 'str');
			$params[] = array(':user_id', $_SESSION['TRACK_LOGGED_IN'], 'int');
			$params[] = array(':token_expires', date("Y-m-d", time() + (3600 * 24 * 365)), 'str');
			$db->query($query, $params);
			setcookie('TRACKER_RM_ID', $auth_token, time() + (3600 * 24 * 365));
		}
		// they are in :)
		print_message('You are logged in', '?page=log');
	}
	else
	{
		$error = true;
	}
}


$template->assign_vars(array(
	'EXERCISEOLD' => $_REQUEST['exercise_name'],
	'EXERCISENEW' => (isset($_POST['exercisenew'])) ? $_POST['exercisenew'] : '',
	'B_ERROR' => $error
	));
$template->set_filenames(array(
		'body' => 'login.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');