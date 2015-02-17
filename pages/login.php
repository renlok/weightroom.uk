<?php
// login
if (isset($_POST['action']) && isset($_POST['username']) && isset($_POST['password']))
{
	if ($user->user_login($_POST['username'], $_POST['password']))
	{
		// they are in :)
		echo "<p>You are logged in</p>";
	}
	else
	{
		echo '<p>Username/password incorrect</p>';
	}
}

echo <<<EOD
<form action="?do=login" method="post">
username:<br>
<input type="text" name="username">
<br>
password:<br>
<input type="password" name="password"><br>
<input type="submit" name="action" value="Login";>
</form>
EOD;
?>