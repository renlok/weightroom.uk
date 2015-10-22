<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

require INCDIR . 'class_graph.php';
$graph = new graph();

$range = (!isset($_GET['range']) || !in_array($_GET['range'], array(1,3,6,12))) ? 0 : $_GET['range'];

$graph_data = $graph->get_graph_data($user->user_id, 'wilks', $range);
$graph_data = $graph->build_coefficient_graph_data($graph_data, 'wilks');
$graph_output = $graph->build_graph_data($graph_data, true);

$template->assign_vars(array(
	'GRAPH_DATA' => $graph_output,
	'GRAPH_TYPE_LC' => 'wilks',
	'GRAPH_TYPE' => 'Wilks',
	'MULTICHART' => true,
	'RANGE' => $range
	));
$template->set_filenames(array(
		'body' => 'graph.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>
