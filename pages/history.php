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
$volume_data = $reps_data = $sets_data = $rm_data = $ai_data = "var dataset = [];\n";

// workout how much reps, sets&RM need to be scaled to match volume
$max_values = $logs[0];
unset($logs[0]);
$reps_scale = floor($max_values['max_vol'] / $max_values['max_reps']);
$sets_scale = floor($max_values['max_vol'] / $max_values['max_sets']);
$rm_scale = floor($max_values['max_vol'] / $max_values['max_rm']);
$ai_scale = floor($max_values['max_vol'] / 100);
// get current pr
$pr_data = $log->get_prs($user->user_id, date("Y-m-d"), $exercise_name);
// build a reference for current 1rm
$pr_weight = max($pr_data);
$reps = array_search($pr_weight, $pr_data);
$current_1rm = $log->generate_rm($pr_weight, $reps);
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
		if ($set['is_pr'] && $set['logitem_reps'] == 1)
		{
			$current_1rm = $set['logitem_weight'];
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
	$average_intensity = (($log['logex_volume']/$log['logex_reps'])/$current_1rm) * 100;
	$template->alter_block_array('items', array('AVG_INT' => round($average_intensity, 1)), true, 'change');
	$ai_data .= "\tdataset.push({x: new Date($date), y: " . ($average_intensity * $ai_scale) . ", shape:'circle'});\n";
}
$volume_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Volume',\n\tcolor:'#b84a68'\n});\n";
$reps_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Total reps',\n\tcolor:'#a6bf50'\n});\n";
$sets_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Total sets',\n\tcolor:'#56c5a6'\n});\n";
$rm_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: '1RM',\n\tcolor:'#765dcb'\n});\n";
$ai_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Average Intensity',\n\tcolor:'#907fcc'\n});\n";

$template->assign_vars(array(
	'EXERCISE' => ucwords($exercise_name),
	'GRAPH_DATA' => $volume_data . $reps_data . $sets_data . $rm_data . $ai_data,
	'REP_SCALE' => $reps_scale,
	'SET_SCALE' => $sets_scale,
	'RM_SCALE' => $rm_scale,
	'AI_SCALE' => $ai_scale
	));
$template->set_filenames(array(
		'body' => 'history.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>