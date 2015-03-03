<?php
if (!(isset($_GET['csrftoken']) && $_GET['csrftoken'] == $_SESSION['csrftoken']))
{
	exit;
}
if (isset($_GET['do']) && $_GET['do'] == 'cal' && isset($_GET['user_id']))
{
	$user_id = $_GET['user_id'];
	if(!$user->is_valid_user($user_id))
		exit;
	$log_data = $log->load_log_list($user_id, $_GET['date']);
	echo '{';
	echo $log->build_log_list($log_list);
	echo '}';
}
?>