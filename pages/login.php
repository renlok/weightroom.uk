<?php
$error = false;

// login
if (isset($_POST['action']) && isset($_POST['username']) && isset($_POST['password']))
{
	if ($user->user_login($_POST['username'], $_POST['password']))
	{
		// they are in :)
<<<<<<< HEAD
		print_message('You are logged in', '?page=log');
=======
		print_message('You are logged in');
>>>>>>> origin/master
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
$template->display('body');
?>