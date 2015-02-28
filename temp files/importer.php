<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// define the file dirs
define('MAINDIR', '../');
define('INCDIR', MAINDIR . 'inc/');
define('PAGEDIR', MAINDIR . 'pages/');
require INCDIR . 'functions_global.php';

// set the database
require INCDIR . 'class_db_handle.php';
$db = new db_handle();
// join the db party
$DbHost = 'localhost';
$DbUser = 'welinkco_tracker';
$DbPassword = 'oP4NBB^V_0qu';
$DbDatabase = 'welinkco_tracker';
$db->connect($DbHost, $DbUser, $DbPassword, $DbDatabase);

require INCDIR . 'class_log.php';
$log = new log();
// load user handler
require INCDIR . 'class_user.php';
$user = new user();

$query = "SELECT * FROM workouts ORDER BY `PerformedAt` DESC , `Exercisenum` ASC , `Set` ASC ";
$db->query($query, array());
$data = array();
while ($row = $db->fetch())
{
	$data[$row['PerformedAt']][] = $row;
	if ($row['IsPR'] == 1 && $row['Reps'] < 10)
	{
		$exercise_id = $log->get_exercise_id(1, $row['Exercise']);
		$trueweight = round(($row['Weight'] * 0.453592), 1);
		$query = "INSERT INTO exercise_records (exercise_id, user_id, pr_date, pr_weight, pr_reps) VALUES ($exercise_id, 1, '{$row['PerformedAt']}', '$trueweight', {$row['Reps']})";
		$db->query($query, array());
	}
}

foreach ($data as $log_date => $logs)
{
	$text = '';
	$first = true;
	$exercise = null;
	$reps = null;
	$sets = 1;
	$weight = null;
	$newexercise = true;
	foreach ($logs as $entry)
	{
		if (($entry['Exercise'] != $exercise) || $first)
		{
			$reps = null;
			$weight = null;
			$newexercise = true;
			$exercise = $entry['Exercise'];
			if ($first)
				$exercisetext = "#{$entry['Exercise']}\n";
			else
				$exercisetext = "\n\n#{$entry['Exercise']}\n";
		}
		if (($weight != $entry['Weight']) || ($reps != $entry['Reps']))
		{
			if (!$first)
			{
				$text .= " x $sets\n";
			}
			$first = false;
			if ($newexercise)
				$text .= $exercisetext;
			$newexercise = false;
			$weight = $entry['Weight'];
			$reps = $entry['Reps'];
			$sets = 1;
			$trueweight = round(($entry['Weight'] * 0.453592), 1);
			$text .= "$trueweight x {$entry['Reps']}";
		}
		else
		{
			$sets++;
		}
	}
	$text .= " x $sets\n";
	echo '<p>'.$text.'</p>';
	$log_data = $log->parse_new_log($text);
	$log->store_new_log_data($log_data, $text, $log_date, 1, 87);
}
?>