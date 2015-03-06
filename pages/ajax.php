<?php
if (!(isset($_GET['csrftoken']) && $_GET['csrftoken'] == $_SESSION['csrftoken']))
{
	//exit;
}

require INCDIR . 'class_log.php';
$log = new log();

if (isset($_GET['do']) && $_GET['do'] == 'cal' && isset($_GET['user_id']))
{
	$user_id = $_GET['user_id'];
	if(!$user->is_valid_user($user_id))
		exit;
	$log_list = $log->load_log_list($user_id, $_GET['date']);
	echo '{"dates":[';
	echo $log->build_log_list($log_list);
	echo "],
	\"cals\": \"{$_GET['date']}\"}";
}
exit();
?>