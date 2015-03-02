<?php
if (!(isset($_GET['csrftoken']) && $_GET['csrftoken'] == $_SESSION['csrftoken']))
{
	exit;
}
?>