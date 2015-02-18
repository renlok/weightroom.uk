<?php
if (!$user->is_logged_in())
{
	echo "You are not loged in?!";
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
		echo "<h1>$exercise</h1><p>Volume: {$log_items['total_volume']} - Reps: {$log_items['total_reps']} - Sets: {$log_items['total_sets']}</p>";
		foreach ($log_items['sets'] as $set)
		{
			echo "<p>{$set['weight']} x {$set['reps']} x {$set['sets']} - {$set['comment']}</p>";
		}
		echo "<p>{$log_items['comment']}</p>";
	}
}
// to add a log or edit a log
elseif ($_GET['do'] == 'edit')
{

}
?>
