<?php

unset($_SESSION['TRACK_LOGGED_IN'], $_SESSION['TRACK_LOGGED_NUMBER'], $_SESSION['TRACK_LOGGED_PASS']);
if (isset($_COOKIE['TRACKER_RM_ID']))
{
	$query = "DELETE FROM auth_tokens WHERE token = :RM_ID";
	$params = array();
	$params[] = array(':RM_ID', $_COOKIE['TRACKER_RM_ID'], 'str');
	$db->query($query, $params);
	setcookie('TRACKER_RM_ID', '', time() - 3600);
}

header('location: index.php');
exit;
?>