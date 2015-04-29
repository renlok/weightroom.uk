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

	if (!$user->is_valid_user($user_id))
	{
		print_message('No user exists', '?page=log');
		exit;
	}

	// deal with the follows
	if (isset($_GET['follow']))
	{
		if ($_GET['follow'] == 'false')
		{
			$user->deletefollower($user_id);
		}
		elseif ($_GET['follow'] == 'true')
		{
			$user->addfollower($user_id);
		}
	}

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
			if ($set['is_bw'] == 0)
			{
				$weight = round($set['weight'], 2);
			}
			else
			{
				if ($set['weight'] != 0)
				{
					$weight = 'BW' . round($set['weight'], 2);
				}
				else
				{
					$weight = 'BW';
				}
			}
			$template->assign_block_vars('items.sets', array(
					'WEIGHT' => $weight,
					'REPS' => $set['reps'],
					'SETS' => $set['sets'],
					'IS_PR' => $set['is_pr'],
					'COMMENT' => trim($set['comment']),
					));
		}
	}
	$log_ic = $log->load_log($user_id, $log_date, 'log_comment, log_id, log_weight');

	require INCDIR . 'class_comments.php';
	$log_comments = new comments();
	// deal with the comments
	$commenting = false;
	if (isset($_POST['log_id']))
	{
		$parent_id = (intval($_POST['parent_id']) == 0) ? NULL : $_POST['parent_id'];
		$log_comments->make_comment($parent_id, $_POST['comment'], $_POST['log_id'], $log_date, $user_id);
		$commenting = true;
	}

	$log_comments->load_log_comments($log_ic['log_id']);
	$log_comments->print_comments();

	// get user info
	$user_data = $user->get_user_data($user_id);
	//create badges
	$badges = '';
	if ($user_data['user_beta'] == 1)
		$badges .= '<img src="img/bug.png" alt="Beta tester">';
	if ($user_data['user_admin'] == 1)
		$badges .= '<img src="img/star.png" alt="Adminnosaurus Rex">';
	
	$timestamp = strtotime($log_date . ' 00:00:00');
	$template->assign_vars(array(
		'LOG_DATES' => $log->build_log_list($log_list),
		'USER_ID' => $user_id,
		'USERNAME' => $user_data['user_name'],

		'B_NOSELF' => ($user_id != $user->user_id),
		'B_FOLLOWING' => $user->is_following($user_id),
		'BADGES' => $badges,
		'JOINED' => $user_data['user_joined'],

		'B_LOG' => (!(empty($log_data) && empty($log_ic['log_comment']))),
		'JSDATE' => ($timestamp * 1000),
		'COMMENT' => $log_ic['log_comment'],
		'DATE' => $log_date,
		'TOMORROW' => date("Y-m-d", $timestamp + 86400),
		'YESTERDAY' => date("Y-m-d", $timestamp - 86400),
		'LOG_ID' => $log_ic['log_id'],
		'LOG_COMMENTS' => $log_comments->comments,
		'COMMENTING' => $commenting
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
	// has anything been submitted?
	if (isset($_POST['log']))
	{
		// get user weight
		if (!isset($_POST['weight']) || strlen($_POST['weight']) == 0 || intval($_POST['weight']) == 0)
		{
			$query = "SELECT log_weight FROM logs WHERE log_date < :log_date AND user_id = :user_id ORDER BY log_date DESC LIMIT 1";
			$params = array(
				array(':user_id', $user->user_id, 'int'),
				array(':log_date', $_GET['date'], 'str')
			);
			$db->query($query, $params);
			if ($db->numrows() == 1)
			{
				$weight = $db->result('log_weight');
			}
			else
			{
				$weight = $user->user_data['user_weight'];
			}
		}
		else
		{
			$weight = floatval($_POST['weight']);
		}
		// set log text
		$log_text = trim($_POST['log']);
		// parse the log
		$log_data = $log->parse_new_log($log_text, $weight);
		$new_prs = $log->store_new_log_data($log_data, $log_text, $log_date, $user->user_id, $weight);
		// check if there are prs
		if (count($new_prs) > 0)
		{
			$pr_string = '';
			foreach ($new_prs as $exercise => $reps)
			{
				foreach ($reps as $rep => $weights)
				{
					foreach ($weights as $weight)
					{
						$pr_string .= "<p>You have set a new <strong>$exercise {$rep}RM</strong> of <strong>$weight</strong> kg</p>";
					}
				}
			}
			print_message($pr_string);
		}
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
			$weight = $log->get_user_weight ($user->user_id, $_GET['date']);
		}
	}

	// build exercise list for editor hints
	$exercises = $log->list_exercises($user->user_id);
	$elist = '';
	foreach ($exercises as $exercise)
	{
		$elist .= "[\"{$exercise['exercise_name']}\", {$exercise['COUNT']}],";
	}
	$template->assign_vars(array(
		'LOG' => (isset($_POST['log'])) ? $_POST['log'] : $log_text,
		'WEIGHT' => $weight,
		'DATE' => $log_date,
		'ERROR' => $error,
		'VALID_LOG' => $valid_log,
		'EXERCISE_LIST' => substr($elist, 0, -1)
		));
	$template->set_filenames(array(
			'body' => 'log_edit.tpl'
			));
	$template->display('header');
	$template->display('body');
	$template->display('footer');
}
?>
