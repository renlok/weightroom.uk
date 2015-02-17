<?php
// check login
// ...

$log_date = ''; // ???
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
?>
