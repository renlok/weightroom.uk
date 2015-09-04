<?php
// get stats, prs, 
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

require INCDIR . 'class_log.php';
$log = new log();

$from_date = (isset($_GET['from'])) ? $_GET['from'] : '';
$to_date = (isset($_GET['to'])) ? $_GET['to'] : '';

$logs = $log->list_exercise_logs($user->user_id, $from_date, $to_date);
$volume_data = $reps_data = $sets_data = "var dataset = [];\n";

// workout how much reps, sets&RM need to be scaled to match volume
$max_values = $logs[0];
unset($logs[0]);
$reps_scale = floor($max_values['max_vol'] / $max_values['max_reps']);
$sets_scale = floor($max_values['max_vol'] / $max_values['max_sets']);
$first_date = $last_date = '';
foreach ($logs as $log)
{
	if ($first_date == '')
		$first_date = $log['logitem_date'];
	$last_date = $log['logitem_date'];
	$date = strtotime($log['logitem_date'] . ' 00:00:00') * 1000;
	$volume_data .= "\tdataset.push({x: new Date($date), y: {$log['logex_volume']}, shape:'circle'});\n";
	$reps_data .= "\tdataset.push({x: new Date($date), y: " . ($log['logex_reps'] * $reps_scale) . ", shape:'circle'});\n";
	$sets_data .= "\tdataset.push({x: new Date($date), y: " . ($log['logex_sets'] * $sets_scale) . ", shape:'circle'});\n";
}
$volume_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Volume',\n\tcolor:'#b84a68'\n});\n";
$reps_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Total reps',\n\tcolor:'#a6bf50'\n});\n";
$sets_data .= "HistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Total sets',\n\tcolor:'#56c5a6'\n});\n";

$from_date = (!empty($from_date)) ? $from_date : $last_date;
$to_date = (!empty($to_date)) ? $to_date : $first_date;
$template->assign_vars(array(
	'GRAPH_DATA' => $volume_data . $reps_data . $sets_data,
	'REP_SCALE' => $reps_scale,
	'SET_SCALE' => $sets_scale,
	'FROM_DATE' => $from_date,
	'TO_DATE' => $to_date,
	'JS_FROM_DATE' => strtotime($from_date . ' 00:00:00') * 1000,
	'JS_TO_DATE' => strtotime($to_date . ' 00:00:00') * 1000
	));
$template->set_filenames(array(
		'body' => 'volume.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>