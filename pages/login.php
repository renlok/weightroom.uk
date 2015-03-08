<?php
$error = false;

// login
if (isset($_POST['action']) && isset($_POST['username']) && isset($_POST['password']))
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
	'USERNAME' => (isset($_POST['username'])) ? $_POST['username'] : '',
	'B_ERROR' => $error
	));
$template->set_filenames(array(
		'body' => 'login.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>