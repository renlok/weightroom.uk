<?php
// login
if (isset($_POST['action']) && isset($_POST['username']) && isset($_POST['password']))
{
	if ($user->user_login($_POST['username'], $_POST['password']))
	{
		// they are in :)
	}
	else
	{
		echo '<p>Username/password incorrect</p>';
	}
}

echo "<form>
username:<br>
<input type=\"text\" name=\"username\">
<br>
password:<br>
<input type=\"text\" name=\"password\">
<input type=\"submit\" name=\"action\" value=\"Login\";>
</form>";
}
?>