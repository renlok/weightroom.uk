<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

function isfloat($f) { return ($f == (string)(float)$f); }

$settings_updated = false;
// save the new settings
if (isset($_POST['action']))
{
	$error = false;
	// check exercises are valid
	if (intval($_POST['squat']) > 0)
	{
		$query = "SELECT exercise_id FROM exercises WHERE exercise_id = :exercise_id AND user_id = :user_id";
		$params = array(
			array(':exercise_id', $_POST['squat'], 'int'),
			array(':user_id', $user->user_id, 'int'),
		);
		$db->query($query, $params);
		if ($db->numrows() == 0)
		{
			$error_msg = 'The Squat exercise you selected does not exist';
			$error = true;
		}
	}
	if (intval($_POST['deadlift']) > 0)
	{
		$query = "SELECT exercise_id FROM exercises WHERE exercise_id = :exercise_id AND user_id = :user_id";
		$params = array(
			array(':exercise_id', $_POST['deadlift'], 'int'),
			array(':user_id', $user->user_id, 'int'),
		);
		$db->query($query, $params);
		if ($db->numrows() == 0)
		{
			$error_msg = 'The Deadlift exercise you selected does not exist';
			$error = true;
		}
	}
	if (intval($_POST['bench']) > 0)
	{
		$query = "SELECT exercise_id FROM exercises WHERE exercise_id = :exercise_id AND user_id = :user_id";
		$params = array(
			array(':exercise_id', $_POST['bench'], 'int'),
			array(':user_id', $user->user_id, 'int'),
		);
		$db->query($query, $params);
		if ($db->numrows() == 0)
		{
			$error_msg = 'The Bench exercise you selected does not exist';
			$error = true;
		}
	}
	if (intval($_POST['snatch']) > 0)
	{
		$query = "SELECT exercise_id FROM exercises WHERE exercise_id = :exercise_id AND user_id = :user_id";
		$params = array(
			array(':exercise_id', $_POST['snatch'], 'int'),
			array(':user_id', $user->user_id, 'int'),
		);
		$db->query($query, $params);
		if ($db->numrows() == 0)
		{
			$error_msg = 'The Snatch exercise you selected does not exist';
			$error = true;
		}
	}
	if (intval($_POST['cnj']) > 0)
	{
		$query = "SELECT exercise_id FROM exercises WHERE exercise_id = :exercise_id AND user_id = :user_id";
		$params = array(
			array(':exercise_id', $_POST['cnj'], 'int'),
			array(':user_id', $user->user_id, 'int'),
		);
		$db->query($query, $params);
		if ($db->numrows() == 0)
		{
			$error_msg = 'The Clean and Jerk exercise you selected does not exist';
			$error = true;
		}
	}
	if (intval($_POST['gender']) != 1 && intval($_POST['gender']) != 0)
	{
		$error_msg = 'Invalid gender selected';
		$error = true;
	}
	if (!isfloat($_POST['bodyweight']))
	{
		$error_msg = 'Bodyweight must be numeric';
		$error = true;
	}
	if (intval($_POST['volumeincfails']) != 1 && intval($_POST['volumeincfails']) != 0)
	{
		$error_msg = 'Invalid option selected';
		$error = true;
	}
	
	if (!$error)
	{
		// update the settings
		$query = "UPDATE users SET
				user_unit = :user_unit,
				user_showreps = :user_showreps,
				user_squatid = :user_squatid,
				user_deadliftid = :user_deadliftid,
				user_benchid = :user_benchid,
				user_snatchid = :user_snatchid,
				user_cleanjerkid = :user_cleanjerkid,
				user_weight = :user_weight,
				user_gender = :user_gender,
				user_volumeincfails = :user_volumeincfails
				WHERE user_id = :user_id";
		$user_showreps = implode('|', array_map('intval', $_POST['showreps']));
		$params = array(
			array(':user_unit', $_POST['weightunit'], 'int'),
			array(':user_showreps', $user_showreps, 'str'),
			array(':user_squatid', $_POST['squat'], 'int'),
			array(':user_deadliftid', $_POST['deadlift'], 'int'),
			array(':user_benchid', $_POST['bench'], 'int'),
			array(':user_snatchid', $_POST['snatch'], 'int'),
			array(':user_cleanjerkid', $_POST['cnj'], 'int'),
			array(':user_weight', $_POST['bodyweight'], 'float'),
			array(':user_gender', $_POST['gender'], 'int'),
			array(':user_volumeincfails', $_POST['volumeincfails'], 'int'),
			array(':user_id', $user->user_id, 'int'),
		);
		$db->query($query, $params);
		$user->user_data['user_unit'] = $_POST['weightunit'];
		$user->user_data['user_showreps'] = $user_showreps;
		$user->user_data['user_squatid'] = $_POST['squat'];
		$user->user_data['user_deadliftid'] = $_POST['deadlift'];
		$user->user_data['user_benchid'] = $_POST['bench'];
		$user->user_data['user_snatchid'] = $_POST['snatch'];
		$user->user_data['user_cleanjerkid'] = $_POST['cnj'];
		$user->user_data['user_weight'] = $_POST['bodyweight'];
		$user->user_data['user_gender'] = $_POST['gender'];
		$user->user_data['user_volumeincfails'] = $_POST['volumeincfails'];
		$settings_updated = true;
	}
}

include INCDIR . 'class_log.php';
$log = new log();
// generate the drop downs
$exercises = $log->list_exercises($user->user_id, false);
foreach ($exercises as $exercise)
{
	$template->assign_block_vars('exercise', array(
			'EXERCISE' => ucwords($exercise['exercise_name']),
			'EXERCISE_ID' => $exercise['exercise_id'],
			));
}

$showreps = array_flip(explode('|', $user->user_data['user_showreps']));
$rep_html = '';
for ($i = 1; $i <= 10; $i++)
{
	$selected = (isset($showreps[$i])) ? ' checked' : '';
	$rep_html .= '<label class="checkbox-inline">
	    <input type="checkbox" name="showreps[]" id="inlineCheckbox' . $i . '" value="' . $i . '"' . $selected . '> ' . $i . ' RM
	  </label>';
}

$template->assign_vars(array(
	'SETTINGS_UPDATED' => $settings_updated,
	'ERROR' => (isset($error_msg)) ? $error_msg : '',
	'GENDER' => $user->user_data['user_gender'],
	'BODYWEIGHT' => $user->user_data['user_weight'],
	'SHOWREPHTML' => $rep_html,
	'SNATCHID' => $user->user_data['user_snatchid'],
	'CLEANJERKID' => $user->user_data['user_cleanjerkid'],
	'DEADLIFTID' => $user->user_data['user_deadliftid'],
	'SQUATID' => $user->user_data['user_squatid'],
	'BENCHID' => $user->user_data['user_benchid'],
	'WEIGHTUNIT' => $user->user_data['user_unit'],
	'VOLUMEINCFAILS' => $user->user_data['user_volumeincfails']
	));
$template->set_filenames(array(
		'body' => 'settings.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>