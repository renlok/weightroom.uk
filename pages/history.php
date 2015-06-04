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

/*
// get current prs
$pr_data = $log->get_prs($user->user_id, date("Y-m-d"), $exercise_name, true);
$pr_true = $pr_data[0];
$pr_dates = $pr_data[1];
$pr_data_max = $pr_data[0];
//check pr data
$highest = 0;
for ($i = 10; $i >= 1; $i--)
{
	if (isset($pr_data_max[$i]))
	{
		if ($pr_true[$i] > $highest)
		{
			$highest = $pr_true[$i];
		}
		if ($pr_data_max[$i] < $highest)
		{
			$pr_data_max[$i] = $highest . '*';
		}
	}
	else
	{
		$pr_data_max[$i] = '--';
		$pr_dates[$i] = 0;
	}
}*/

$logs = $log->list_exercise_logs($user->user_id, $exercise_name);
foreach ($logs as $log)
{
	$template->assign_block_vars('items', array(
			'LOG_DATE' => $log['logitem_date'],
			'VOLUME' => $row['logex_volume'],
			'REPS' => $row['logex_reps'],
			'SETS' => $row['logex_sets'],
			'COMMENT' => $row['logex_comment']
			));
	foreach ($log['sets'] as $set)
	{
		if ($set['is_bw'] == 0)
		{
			$weight = round($set['logitem_weight'], 2);
		}
		else
		{
			if ($set['weight'] != 0)
			{
				$weight = 'BW' . round($set['logitem_weight'], 2);
			}
			else
			{
				$weight = 'BW';
			}
		}
		$template->assign_block_vars('items.sets', array(
				'WEIGHT' => $weight,
				'REPS' => $set['logitem_reps'],
				'SETS' => $set['logitem_sets'],
				'COMMENT' => $set['logitem_comment'],
				'IS_BW' => $set['is_bw'],
				'IS_PR' => $set['is_pr'],
				));
	}
}

$template->assign_vars(array(
	'EXERCISE' => ucwords($exercise_name),
	));
$template->set_filenames(array(
		'body' => 'exercise.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>