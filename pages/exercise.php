<?php
// get stats, prs, 
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

require INCDIR . 'class_log.php';
$log = new log();

$exercise_name = (isset($_GET['ex'])) ? $_GET['ex'] : '';

if(!$log->is_valid_exercise($user->user_id, $exercise_name))
{
	print_message('Invalid exercise', '?page=exercise&do=list');
	exit;
}

if ($_GET['do'] == 'list')
{
	
}
else
{
	// get current prs
	$pr_data = $log->get_prs($user->user_id, date("Y-m-d"), $exercise_name);
	//check pr data
	$highest = 0;
	for ($i = 10; $i >= 1; $i--)
	{
		if (isset($pr_data[$i]))
		{
			if ($pr_data[$i] < $highest)
			{
				$pr_data[$i] = $highest . '*';
			}
		}
		else
		{
			$pr_data[$i] = '--';
		}
	}
	
	$full_pr_data = $log->get_prs_data($user->user_id, $exercise_name);
	$graph_data = $log->build_pr_graph_data($full_pr_data);
	
	$template->assign_vars(array(
		'PR_DATA' => $pr_data,
		'GRAPH_DATA' => $graph_data,
		'EXERCISE' => ucwords($exercise_name)
		));
	$template->set_filenames(array(
			'body' => 'exercise.tpl'
			));
	$template->display('body');
}
?>