<?php
// tooooools
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

$template->set_filenames(array(
		'body' => 'tools.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>