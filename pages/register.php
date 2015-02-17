<?php

if (isset($_POST['action']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']))
{
	// In this case, the password is retrieved from a form input
	$password = $_POST["password"];

	// Passwords should never be longer than 72 characters to prevent DoS attacks
	if (strlen($password) > 72) { die("Password must be 72 characters or less"); }
	
	// check if username / email have been used before
	$query = "SELECT user_id FROM users WHERE user_email = :user_email OR user_name = :user_name";
	$params = array();
	$params[] = array(':user_email', $_POST['email'], 'str');
	$params[] = array(':user_name', $_POST['username'], 'str');
	$db->query($query, $params);
	if ($db->numrows() > 0)
	{
		$ERR = $ERR_115; // E-mail already used
	}

	// The $hash variable will contain the hash of the password
	require INCDIR . 'PasswordHash.php';
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
	}
	else
	{
		die('registration faied');
	}
}

echo <<<EOD
<form action="?do=register" method="post">
username:<br>
<input type="text" name="username">
<br>
password:<br>
<input type="password" name="password">
<br>
email:<br>
<input type="text" name="email"><br>
<input type="submit" name="action" value="Register";>
</form>
EOD;

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