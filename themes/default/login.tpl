<!-- IF B_ERROR -->
<p>Username/password incorrect</p>
<!-- ENDIF -->
<form action="?page=login" method="post">
username:<br>
<input type="text" name="username" value="{USERNAME}">
<br>
password:<br>
<input type="password" name="password"><br>
<input type="hidden" name="csrftoken" value="{_CSRFTOKEN}">
<input type="submit" name="action" value="Login">
</form>