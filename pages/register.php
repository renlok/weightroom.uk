<?php

// In this case, the password is retrieved from a form input
$password = $_POST["password"];

// Passwords should never be longer than 72 characters to prevent DoS attacks
if (strlen($password) > 72) { die("Password must be 72 characters or less"); }

// The $hash variable will contain the hash of the password
require INCDIR . 'PasswordHash.php';
$hasher = new PasswordHash(8, false);
$hash = $hasher->HashPassword($password);

if (strlen($hash) >= 20) {
	// register that bitch!
	$query = "INSERT INTO users (user_name, user_pass, user_email) VALUES (:user_name, :user_pass, :user_email)";
	$params = array('user_name' => ,
					'user_pass' => ,
					'user_email' => ,);
	$db->query($query, $params);
} else {

 // something went wrong

}

?>
