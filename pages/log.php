<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

require INCDIR . 'class_log.php';
$log = new log();

$log_date = (isset($_GET['date'])) ? $_GET['date'] : date("Y-m-d");

if (!isset($_GET['do']) || (isset($_GET['do']) && $_GET['do'] == 'view'))
{
	$user_id = (isset($_GET['user_id'])) ? $_GET['user_id'] : $user->user_id;
	$log_list = $log->load_log_list($user_id, $log_date);

	$log_data = $log->get_log_data($user_id, $log_date);

	// loop through the exercises
	foreach ($log_data as $exercise => $log_items)
	{
		$template->assign_block_vars('items', array(
				'EXERCISE' => ucwords($exercise),
				'VOLUME' => round($log_items['total_volume'], 2),
				'REPS' => $log_items['total_reps'],
				'SETS' => $log_items['total_sets'],
				'COMMENT' => trim($log_items['comment']),
				));
		foreach ($log_items['sets'] as $set)
		{
			$template->assign_block_vars('items.sets', array(
					'WEIGHT' => round($set['weight'], 2),
					'REPS' => $set['reps'],
					'SETS' => $set['sets'],
					'IS_PR' => $set['is_pr'],
					'COMMENT' => trim($set['comment']),
					));
		}
	}
	$log_ic = $log->load_log($user_id, $log_date, 'log_comment, log_id, log_weight');
	/*require INCDIR . 'class_comments.php';
	$log_comments = new comments();
	$log_comments->load_log_comments($log_ic['log_id']);
	$log_comments->print_comments();*/
	$timestamp = strtotime($log_date . ' 00:00:00');
	$template->assign_vars(array(
		'LOG_DATES' => $log->build_log_list($log_list),
		'B_LOG' => (!(empty($log_data) && empty($comment['log_comment']))),
		'JSDATE' => ($timestamp * 1000),
		'COMMENT' => $log_ic['log_comment'],
		'DATE' => $log_date,
		'TOMORROW' => date("Y-m-d", $timestamp + 86400),
		'YESTERDAY' => date("Y-m-d", $timestamp - 86400),
		));
	$template->set_filenames(array(
			'body' => 'log_view.tpl'
			));
	$template->display('header');
	$template->display('body');
	$template->display('footer');
}
// to add a log or edit a log
elseif ($_GET['do'] == 'edit')
{
	$error = false;
	$log_text = '';
	$weight = (isset($_POST['weight'])) ? floatval($_POST['weight']) : $user->user_data['user_weight'];
	// has anything been submitted?
	if (isset($_POST['log']))
	{
		$log_text = $_POST['log'];
		// parse the log
		$log_data = $log->parse_new_log($log_text, $weight);
		$log->store_new_log_data($log_data, $log_text, $log_date, $user->user_id, $weight);
		print_message('Log processed', '?page=log&do=view&date=' . $log_date);
	}
	// editing a log? try to load the old data
	if (isset($_GET['date']))
	{
		// check log is real
		$valid_log = $log->is_valid_log($user->user_id, $_GET['date']);
		if($valid_log)
		{
			// load log data
			$log_data = $log->load_log($user->user_id, $_GET['date']);
			$log_text = $log_data['log_text'];
			$weight = $log_data['log_weight'];
		}
		else
		{
			$log_text = '';
			$weight = '';
		}
	}
	$template->assign_vars(array(
		'LOG' => (isset($_POST['log'])) ? $_POST['log'] : $log_text,
		'WEIGHT' => $weight,
		'DATE' => $log_date,
		'ERROR' => $error,
		'VALID_LOG' => $valid_log
		));
	$template->set_filenames(array(
			'body' => 'log_edit.tpl'
			));
	$template->display('header');
	$template->display('body');
	$template->display('footer');
}
?>
