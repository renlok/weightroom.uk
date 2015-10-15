<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

require INCDIR . 'class_log.php';
$log = new log();

$range = (!isset($_GET['range']) || !in_array($_GET['range'], array(1,3,6,12))) ? 0 : $_GET['range'];

$full_pr_data = $log->get_bodyweight($user->user_id, 'sinclair', $range);
$graph_data = $log->build_bodyweight_graph_data($full_pr_data); // TODO rewrite this function

$template->assign_vars(array(
	'GRAPH_DATA' => $graph_data,
	'RANGE' => $range
	));
$template->set_filenames(array(
		'body' => 'bodyweight.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>
