<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

$username = (isset($_POST['username'])) ? $_POST['username'] : '';

// get the users
$user_list = $user->search_users($username);
foreach ($user_list as $user_data)
{
	$template->assign_block_vars('user', array(
			'USER_ID' => $user_data['user_id'],
			'USER_NAME' => $user_data['user_name'],
			));
}

$template->assign_vars(array(
		'USER_NAME' => $username
		));
$template->set_filenames(array(
		'body' => 'search.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>
