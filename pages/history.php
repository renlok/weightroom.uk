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

$from_date = (isset($_GET['from'])) ? $_GET['from'] : '';
$to_date = (isset($_GET['to'])) ? $_GET['to'] : '';

$logs = $log->list_exercise_logs($user->user_id, $from_date, $to_date, $exercise_name);
$volume_data = $reps_data = $sets_data = $rm_data = "var dataset = [];\n";

// workout how much reps, sets&RM need to be scaled to match volume
$max_values = $logs[0];
unset($logs[0]);
$reps_scale = floor($max_values['max_vol'] / $max_values['max_reps']);
$sets_scale = floor($max_values['max_vol'] / $max_values['max_sets']);
$rm_scale = floor($max_values['max_vol'] / $max_values['max_rm']);
foreach ($logs as $log)
{
	$template->assign_block_vars('items', array(
			'LOG_DATE' => $log['logitem_date'],
			'VOLUME' => $log['logex_volume'],
			'REPS' => $log['logex_reps'],
			'SETS' => $log['logex_sets'],
			'COMMENT' => $log['logex_comment'],
			));
	$date = strtotime($log['logitem_date'] . ' 00:00:00') * 1000;
	$volume_data .= "\tdataset.push({x: new Date($date), y: {$log['logex_volume']}, shape:'circle'});\n";
	$reps_data .= "\tdataset.push({x: new Date($date), y: " . ($log['logex_reps'] * $reps_scale) . ", shape:'circle'});\n";
	$sets_data .= "\tdataset.push({x: new Date($date), y: " . ($log['logex_sets'] * $sets_scale) . ", shape:'circle'});\n";
	$rm_data .= "\tdataset.push({x: new Date($date), y: " . ($log['logex_1rm'] * $rm_scale) . ", shape:'circle'});\n";
	foreach ($log['sets'] as $set)
	{
		$showunit = true;
		if ($set['is_bw'] == 0)
		{
			$weight = $set['logitem_weight'];
		}
		else
		{
			if ($set['logitem_weight'] != 0)
			{
				if ($set['logitem_weight'] < 0)
				{
					$weight = 'BW - ' . abs($set['logitem_weight']);
				}
				else
				{
					$weight = 'BW + ' . $set['logitem_weight'];
				}
			}
			else
			{
				$weight = 'BW';
				$showunit = false;
			}
		}
		$template->assign_block_vars('items.sets', array(
				'WEIGHT' => $weight,
				'REPS' => $set['logitem_reps'],
				'SETS' => $set['logitem_sets'],
				'RPES' => $set['logitem_rpes'],
				'COMMENT' => $set['logitem_comment'],
				'IS_PR' => $set['is_pr'],
				'SHOW_UNIT' => $showunit,
				'EST1RM' => $set['est1rm'],
				));
	}
}
$volume_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Volume',\n\tcolor:'#b84a68'\n});\n";
$reps_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Total reps',\n\tcolor:'#a6bf50'\n});\n";
$sets_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Total sets',\n\tcolor:'#56c5a6'\n});\n";
$rm_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: '1RM',\n\tcolor:'#765dcb'\n});\n";

$template->assign_vars(array(
	'EXERCISE' => ucwords($exercise_name),
	'GRAPH_DATA' => $volume_data . $reps_data . $sets_data . $rm_data,
	'REP_SCALE' => $reps_scale,
	'SET_SCALE' => $sets_scale,
	'RM_SCALE' => $rm_scale
	));
$template->set_filenames(array(
		'body' => 'history.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>