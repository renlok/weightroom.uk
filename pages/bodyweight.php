<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

require INCDIR . 'class_graph.php';
$graph = new graph();

$range = (!isset($_GET['range']) || !in_array($_GET['range'], array(1,3,6,12))) ? 0 : $_GET['range'];

$graph_data = $graph->get_graph_data($user->user_id, '', $range);
$graph_output = $graph->build_graph_data($graph_data);

$template->assign_vars(array(
	'GRAPH_DATA' => $graph_output,
	'GRAPH_TYPE_LC' => 'bodyweight',
	'GRAPH_TYPE' => 'Bodyweigt',
	'MULTICHART' => false,
	'RANGE' => $range
	));
$template->set_filenames(array(
		'body' => 'graph.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>
