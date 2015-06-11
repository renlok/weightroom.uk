<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

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
			$error = true;
		}
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
				user_cleanjerkid = :user_cleanjerkid
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
	'SHOWREPHTML' => $rep_html ,
	'SNATCHID' => $user->user_data['user_snatchid'],
	'CLEANJERKID' => $user->user_data['user_cleanjerkid'],
	'DEADLIFTID' => $user->user_data['user_deadliftid'],
	'SQUATID' => $user->user_data['user_squatid'],
	'BENCHID' => $user->user_data['user_benchid'],
	'WEIGHTUNIT' => $user->user_data['user_unit']
	));
$template->set_filenames(array(
		'body' => 'settings.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>