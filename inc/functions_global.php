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

function foward($url)
{
	header("Location: $url");
	exit;
}

function generateToken($length = 20)
{
    $buf = '';
    for ($i = 0; $i < $length; ++$i) {
        $buf .= chr(mt_rand(0, 255));
    }
    return bin2hex($buf);
}

function load_global_tempalte()
{
	global $template;
	$template->set_filenames(array(
			'header' => 'global_header.tpl',
			'footer' => 'global_footer.tpl'
			));
}
?>