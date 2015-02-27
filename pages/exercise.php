<?php
// get stats, prs, 
if (!$user->is_logged_in())
{
	print_message('You are not loged in', 'login');
	exit;
}

require INCDIR . 'class_log.php';
$log = new log();

$exercise_name = (isset($_GET['exercise'])) ? $_GET['exercise'] : '';

if(!$log->is_valid_exercise($user->user_id, $exercise_name))
{
	print_message('Invalid exercise', '?page=exercise');
	exit;
}

// get current prs
$pr_data = $log->get_prs($user->user_id, date("Y-m-d"), $exercise_name);
//check pr data
for ($i = 1; $i <= 10; $i++)
{
	$pr_data[$i] = (isset($pr_data[$i])) ? $pr_data[$i] : '--';
}
$template->assign_vars(array(
	'PR_DATA' => $pr_data,
	'EXERCISE' => $exercise_name
	));
$template->set_filenames(array(
		'body' => 'exercise.tpl'
		));
$template->display('body');
?>