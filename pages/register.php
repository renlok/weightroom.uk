<?php
$error = false;
$error_msg = '';
$needinv = true;

if (isset($_POST['action']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']))
{
	// In this case, the password is retrieved from a form input
	$password = $_POST["password"];
	if ($needinv)
	{
		if (!isset($_POST['invcode']) || strlen($_POST['invcode']) != 32)
		{
			$error_msg = "You need a valid invite code";
			$error = true;
		}
		else
		{
			// check if is a valid invite code
			$query = "SELECT code_id FROM invite_codes WHERE code = :code AND code_uses > 0";
			$params = array();
			$params[] = array(':code', $_POST['invcode'], 'str');
			$db->query($query, $params);
			if ($db->numrows() == 0)
			{
				$error_msg = "Invalid invite code";
				$error = true;
			}
		}
	}

	// Passwords should never be longer than 72 characters to prevent DoS attacks
	if (strlen($password) > 72 && !$error)
	{
		$error_msg = "Password must be 72 characters or less";
		$error = true;
	}

	if (!$error)
	{
		// check if username / email have been used before
		$query = "SELECT user_id FROM users WHERE user_email = :user_email OR user_name = :user_name";
		$params = array();
		$params[] = array(':user_email', $_POST['email'], 'str');
		$params[] = array(':user_name', $_POST['username'], 'str');
		$db->query($query, $params);
		if ($db->numrows() > 0)
		{
			$error_msg = "An account is already registered with that email or username";
			$error = true;
		}
		else
		{
			// The $hash variable will contain the hash of the password
			include_once INCDIR . 'PasswordHash.php';
			$hasher = new PasswordHash(8, false);
			$hash = $hasher->HashPassword($password);

			if (strlen($hash) >= 20)
			{
				// register that bitch!
				$query = "INSERT INTO users (user_name, user_pass, user_email, user_hash) VALUES (:user_name, :user_pass, :user_email, :user_hash)";
				$params = array(
					array(':user_name', $_POST['username'], 'str'),
					array(':user_pass', $hash, 'str'),
					array(':user_email', $_POST['email'], 'str'),
					array(':user_hash', get_hash(), 'str'),
				);
				$db->query($query, $params);
				// invite code was used
				if ($needinv)
				{
					$query = "UPDATE invite_codes SET code_uses = code_uses - 1 WHERE code = :code";
					$params = array();
					$params[] = array(':code', $_POST['invcode'], 'str');
					$db->query($query, $params);
				}
				// log that bitch in!
				$user->user_login($_POST['username'], $_POST['password']);
				print_message('You have registered', '?page=log');
				exit;
			}
			else
			{
				$error_msg = "Registration failed";
				$error = true;
			}
		}
	}
}

$template->assign_vars(array(
	'USERNAME' => (isset($_POST['username'])) ? $_POST['username'] : '',
	'EMAIL' => (isset($_POST['email'])) ? $_POST['email'] : '',
	'B_ERROR' => $error,
	'ERROR' => $error_msg
	));
$template->set_filenames(array(
		'body' => 'register.tpl'
		));
$template->display('header');
$template->display('body');
$template->display('footer');

function get_hash()
{
	$string = '0123456789abcdefghijklmnopqrstuvyxz';
	$hash = '';
	for ($i = 0; $i < 5; $i++)
	{
		$rand = rand(0, (34 - $i));
		$hash .= $string[$rand];
		$string = str_replace($string[$rand], '', $string);
	}
	return $hash;
}
?>
