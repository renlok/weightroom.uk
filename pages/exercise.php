<?php
// get stats, prs, 
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

require INCDIR . 'class_log.php';
$log = new log();

if (isset($_GET['do']) && $_GET['do'] == 'compare')
{
	$exercises_exp = (isset($_GET['ex']) ? $_GET['ex'] : array());
	$exercises_exp = array_map('strtolower', $exercises_exp);
	$exercises = array(
		0 => (isset($exercises_exp[0])) ? $exercises_exp[0] : '',
		1 => (isset($exercises_exp[1])) ? $exercises_exp[1] : '',
		2 => (isset($exercises_exp[2])) ? $exercises_exp[2] : '',
		3 => (isset($exercises_exp[3])) ? $exercises_exp[3] : '',
		4 => (isset($exercises_exp[4])) ? $exercises_exp[4] : '',
		);
	$reps = (isset($_GET['reps'])) ? intval($_GET['reps']) : 0;

	for ($i = 0, $count = count($exercises); $i < $count; $i++)
	{
		if(!$log->is_valid_exercise($user->user_id, $exercises[$i]) && $exercises[$i] != '')
		{
			print_message('Invalid exercise', '?page=exercise&do=list');
			exit;
		}
	}

	$full_pr_data = $log->get_prs_data_compare($user->user_id, $reps, $exercises[0], $exercises[1], $exercises[2], $exercises[3], $exercises[4]);
	$graph_data = $log->build_pr_graph_data($full_pr_data, 'ex');

	// load selection data
	$exercises = $log->list_exercises($user->user_id);
	foreach ($exercises as $exercise)
	{
		$template->assign_block_vars('exercise', array(
				'SELECTED' => (in_array($exercise['exercise_name'], $exercises_exp)),
				'EXERCISE' => ucwords($exercise['exercise_name']),
				'COUNT' => $exercise['COUNT'],
				));
	}
	
	$template->assign_vars(array(
		'GRAPH_DATA' => $graph_data,
		'B_SELECTED' => (count($exercises_exp) > 0)
		));
	$template->set_filenames(array(
			'body' => 'exercise_compare.tpl'
			));
	$template->display('header');
	$template->display('body');
	$template->display('footer');
}
elseif ((isset($_GET['do']) && $_GET['do'] == 'list') || !isset($_GET['ex']))
{
	$exercises = $log->list_exercises($user->user_id);
	foreach ($exercises as $exercise)
	{
		$template->assign_block_vars('exercise', array(
				'EXERCISE' => ucwords($exercise['exercise_name']),
				'COUNT' => $exercise['COUNT'],
				));
	}
	$template->set_filenames(array(
			'body' => 'exercise_list.tpl'
			));
	$template->display('header');
	$template->display('body');
	$template->display('footer');
}
else
{
	$exercise_name = (isset($_GET['ex'])) ? $_GET['ex'] : '';

	if(!$log->is_valid_exercise($user->user_id, $exercise_name))
	{
		print_message('Invalid exercise', '?page=exercise&do=list');
		exit;
	}

	// get current prs
	$pr_true = $pr_data = $log->get_prs($user->user_id, date("Y-m-d"), $exercise_name);
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
		if ($pr_true[$i] > $highest)
		{
			$highest = $pr_true[$i];
		}
	}

	$full_pr_data = $log->get_prs_data($user->user_id, $exercise_name);
	$graph_data = $log->build_pr_graph_data($full_pr_data);
	
	$template->assign_vars(array(
		'PR_DATA' => $pr_data,
		'TRUE_PR_DATA' => $pr_true,
		'GRAPH_DATA' => $graph_data,
		'EXERCISE' => ucwords($exercise_name)
		));
	$template->set_filenames(array(
			'body' => 'exercise.tpl'
			));
	$template->display('header');
	$template->display('body');
	$template->display('footer');
}
?>