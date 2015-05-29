<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}
exit;
require INCDIR . 'class_log.php';
$log = new log();

if(!$log->is_valid_exercise($user->user_id, $_GET['exercise_name']))
{
	print_message('Invalid exercise', '?page=exercise&do=list');
	exit;
}

$error = '';
// rename
if (isset($_POST['exercisenew']) && isset($_GET['exercise_name']))
{
echo "$user->user_id, {$_POST['exercisenew']}";
	// is existing exercise
	if($log->is_valid_exercise($user->user_id, $_POST['exercisenew']))
	{
		// merge exercises
		echo $exercise_id_old = $log->get_exercise_id($user->user_id, $_GET['exercise_name']);
		echo $exercise_id_new = $log->get_exercise_id($user->user_id, $_POST['exercisenew']);
		// update the exercise id
		$query = "UPDATE log_exercises SET exercise_id = :exercise_id_new WHERE exercise_id = :exercise_id_old";
		$params = array(
			array(':exercise_id_new', $exercise_id_new, 'int'),
			array(':exercise_id_old', $exercise_id_old, 'int')
		);
		$db->query($query, $params);
		// update PRs
		require INCDIR . 'class_cron.php';
		$cron = new cron();
		$cron->fix_prs_with_id($exercise_id_new);
		// delete the old exercise
		$query = "DELETE FROM exercises WHERE exercise_id = :exercise_id_old";
		$params = array(
			array(':exercise_id_old', $exercise_id_old, 'int')
		);
		$db->query($query, $params);
		// delete the old PRs
		$query = "DELETE FROM exercise_records WHERE exercise_id = :exercise_id_old";
		$params = array(
			array(':exercise_id_old', $exercise_id_old, 'int')
		);
		$db->query($query, $params);
	}
	else
	{
		echo $exercise_id_old = $log->get_exercise_id($user->user_id, $_GET['exercise_name']);
		// just rename it
		$query = "UPDATE exercises SET exercise_name = :exercise_name_new WHERE exercise_id = :exercise_id";
		$params = array(
			array(':exercise_name_new', $_POST['exercisenew'], 'int'),
			array(':exercise_id', $exercise_id_old, 'int')
		);
		$db->query($query, $params);
	}

	// update the log texts
	echo $query = "SELECT l.log_text, l.log_id FROM logs l
			LEFT JOIN log_exercises le ON (le.log_id = l.log_id)
			WHERE l.user_id = :user_id AND le.exercise_id = :exercise_id";
	$params = array(
		array(':user_id', $user->user_id, 'int'),
		array(':exercise_id', $exercise_id_old, 'int')
	);
	var_dump($params);
	$db->query($query, $params);
	while ($row = $db->fetch())
	{
		$new_log = preg_replace("/#\s*({$_GET['exercise_name']})/m", "#" . $_POST['exercisenew'], $row['log_text']);
		echo " preg_replace(\"/#\s*({$_GET['exercise_name']})/m\", \"#\" . {$_POST['exercisenew']}, {$row['log_text']})";
		$query = "UPDATE logs SET log_text = :log_text WHERE log_id = :log_id";
		$params = array(
			array(':log_text', $new_log, 'str'),
			array(':log_id', $row['log_id'], 'int')
		);
		$db->query($query, $params);
	}
}


$template->assign_vars(array(
	'EXERCISEOLD' => $_REQUEST['exercise_name'],
	'EXERCISENEW' => (isset($_POST['exercisenew'])) ? $_POST['exercisenew'] : '',
	'B_ERROR' => $error
	));
$template->set_filenames(array(
		'body' => 'edit_exercise.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');