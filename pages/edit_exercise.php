<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

require INCDIR . 'class_log.php';
$log = new log();

if(!$log->is_valid_exercise($user->user_id, $_GET['exercise_name']))
{
	print_message('Invalid exercise', '?page=exercise&do=list');
	exit;
}

// rename
if (isset($_POST['exercisenew']) && isset($_GET['exercise_name']))
{
exit();
	// is existing exercise
	if($log->is_valid_exercise($user->user_id, $_POST['exercisenew']))
	{
		// merge exercises
		$exercise_id = $log->get_exercise_id($user_id, $_POST['exercisenew']);
		//
	}
	else
	{
		$exercise_id = $log->get_exercise_id($user_id, $_GET['exercise_name']);
		// just rename it
		$query = "UPDATE log_exercises SET exercise_name = :exercise_name_new WHERE exercise_id = :exercise_id";
		$params = array(
			array(':exercise_name_new', $_POST['exercisenew'], 'int'),
			array(':exercise_id', $exercise_id, 'int')
		);
		$db->query($query, $params);
		// update the log texts
		$query = "SELECT l.log_text, l.log_id FROM logs l
				LEFT JOIN log_exercises le ON (le.log_id = l.log_id)
				WHERE l.user_id = :user_id AND le.exercise_id = :exercise_id";
		$params = array(
			array(':user_id', $user_id, 'int'),
			array(':exercise_id', $user_id, 'int')
		);
		$db->query($query, $params);
		while ($row = $db->fetch())
		{
			$new_log = preg_replace("/ #\s(exersece)/g", "#" . $_POST['exercisenew'], $row['log_text']);
			$query = "UPDATE logs SET log_text = :log_text WHERE log_id = :log_id";
			$params = array(
				array(':log_text', $new_log, 'str'),
				array(':log_id', $row['log_id'], 'int')
			);
			$db->query($query, $params);
		}
	}
}


$template->assign_vars(array(
	'EXERCISEOLD' => $_REQUEST['exercise_name'],
	'EXERCISENEW' => (isset($_POST['exercisenew'])) ? $_POST['exercisenew'] : '',
	'B_ERROR' => $error
	));
$template->set_filenames(array(
		'body' => 'login.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');