<?php
if (!$user->is_logged_in())
{
	print_message('You are not loged in', '?page=login');
	exit;
}

$query = "SELECT code, code_uses, code_expire FROM invite_codes WHERE user_id = :user_id OR user_id = 0";
$params = array(
	array(':user_id', $user->user_id, 'int')
);
$db->query($query, $params);
$invites = $db->fetchall();
foreach ($invites as $invite)
{
	$template->assign_block_vars('invites', array(
			'INVITE_CODE' => $invite['code'],
			'REMAIN' => $invite['code_uses'],
			'EXPIRES' => $invite['code_expire'],
			));
}
$template->set_filenames(array(
		'body' => 'invites.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');
?>