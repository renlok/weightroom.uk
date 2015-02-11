http://sunnyis.me/blog/secure-passwords/
<?php

// In this case, the password is retrieved from a form input
$password = $_POST["password"];

// Passwords should never be longer than 72 characters to prevent DoS attacks
if (strlen($password) > 72) { die("Password must be 72 characters or less"); }

// The $hash variable will contain the hash of the password
require("PasswordHash.php");
$hasher = new PasswordHash(8, false);
$hash = $hasher->HashPassword($password);

if (strlen($hash) >= 20) {

 // Store the hash somewhere such as a database
 // The code for that is up to you as this tutorial only focuses on hashing passwords

} else {

 // something went wrong

}

?>
