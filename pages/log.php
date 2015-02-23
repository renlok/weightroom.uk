<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', 'login');
	exit;
}

if (!isset($_GET['do']) || (isset($_GET['do']) && $_GET['do'] == 'view'))
{
	$log_date = (isset($_GET['date'])) ? $_GET['date'] : date("Y-m-d H:i:s");
	$user_id = (isset($_GET['user_id'])) ? $_GET['user_id'] : $user->user_id;

	$log_data = $log->get_log_data($user_id, $log_date);

	// loop through the exercises
	foreach ($log_data as $exercise => $log_items)
	{
		$template->assign_block_vars('items', array(
				'EXERCISE' => $exercise,
				'VOLUME' => $log_items['total_volume'],
				'REPS' => $log_items['total_reps'],
				'SETS' => $log_items['total_sets'],
				'COMMENT' => $log_items['comment'],
				));
		foreach ($log_items['sets'] as $set)
		{
			$template->assign_block_vars('items.sets', array(
					'WEIGHT' => $set['weight'],
					'REPS' => $set['reps'],
					'SETS' => $set['sets'],
					'COMMENT' => $set['comment'],
					));
		}
	}
	$template->set_filenames(array(
			'body' => 'log_view.tpl'
			));
	$template->display('body');
}
// to add a log or edit a log
elseif ($_GET['do'] == 'edit')
{
	$error = false;
	$log_text = '';
	$date = (isset($_GET['date'])) ? $_GET['date'] : date("Y-m-d");
	$weight = (isset($_POST['weight'])) ? floatval($_POST['weight']) : $user->user_data['user_weight'];
	// has anything been submitted?
	if (isset($_POST['log']))
	{
		$log_text = $_POST['log'];
		// parse the log
		$log_data = $log->parse_new_log($log_text);
		$log->store_new_log_data($log_data, $log_text, $date, $user->user_id, $weight);
	}
	// edit log?
	if (isset($_GET['date']))
	{
		// check log is real
		if(!$log->is_valid_log($user->user_id, $_GET['date']))
		{
			print_message('Invalid log entry', 'log');
			exit;
		}
		// load log data
		$log_data = $log->load_log($user->user_id, $_GET['date']);
		$log_text = $log_data['log_text'];
		$weight = $log_data['log_weight'];
	}
	$template->assign_vars(array(
		'LOG' => (isset($_POST['log'])) ? $_POST['log'] : $log_text,
		'WEIGHT' => $weight,
		'DATE' => $date,
		'B_ERROR' => $error
		));
	$template->set_filenames(array(
			'body' => 'log_edit.tpl'
			));
	$template->display('body');
}
?>
