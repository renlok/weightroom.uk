<?php
$error = false;

// login
if (isset($_POST['action']) && isset($_POST['username']) && isset($_POST['password']))
{
	if ($user->user_login($_POST['username'], $_POST['password']))
	{
		// they are in :)
		$template->assign_vars(array(
				'MESSAGE' => "<p>You are logged in</p>",
				));
		$template->set_filenames(array(
				'body' => 'message.tpl'
				));
		$template->display('body');
	}
	else
	{
		$error = true;
	}
}

$template->assign_vars(array(
	'USERNAME' => (isset($_POST['username'])) ? $_POST['username'] : '',
	'B_ERROR' => true
	));
$template->set_filenames(array(
		'body' => 'login.tpl'
		));
$template->display('body');
?>