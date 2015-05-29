<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

include INCDIR . 'class_dash.php';
$dash = new dash();
$dash_data = $dash->get_dash_data($user->user_id);

foreach ($dash_data as $dash_items)
{
	$template->assign_block_vars('logs', array(
			'USER_NAME' => $dash_items['user_name'],
			'USER_ID' => $dash_items['user_id'],
			'POSTED' => $dash_items['posted'],
			'LOG_DATE' => $dash_items['log_date']
			));
}
$template->set_filenames(array(
		'body' => 'dash.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>