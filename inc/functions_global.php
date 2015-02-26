<?php
function print_message($message, $forward = '')
{
	global $template;
	$template->assign_vars(array(
			'MESSAGE' => $message,
			'FORWARD' => $forward
			));
	$template->set_filenames(array(
			'body' => 'message.tpl'
			));
	$template->display('body');
}
<<<<<<< HEAD

function foward($url)
{
	header("Location: $url");
	exit;
}
=======
>>>>>>> origin/master
?>