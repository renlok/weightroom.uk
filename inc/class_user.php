<?php
class user
{
	var $user_data, $user_id, $logged_in;

	function __construct()
	{
		global $_SESSION, $db;

		$this->logged_in = false;
		$this->can_sell = false;
		$this->can_buy = false;
		$this->user_data = array();

		if (isset($_SESSION['TRACK_LOGGED_NUMBER']) && isset($_SESSION['TRACK_LOGGED_IN']) && isset($_SESSION['TRACK_LOGGED_PASS']))
		{
			$query = "SELECT * FROM users WHERE user_pass = :user_pass AND user_id = :user_id";
			$params = array(
				array(':user_pass', $_SESSION['TRACK_LOGGED_PASS'], 'str'),
				array(':user_id', $_SESSION['TRACK_LOGGED_IN'], 'int')
			);
			$db->query($query, $params);

			if ($db->numrows() > 0)
			{
				$user_data = $db->result();

				if (strspn($user_data['user_pass'], $user_data['user_hash']) == $_SESSION['TRACK_LOGGED_NUMBER'])
				{
					$this->logged_in = true;
					$this->user_data = $user_data;
					$this->user_id = $user_data['user_id'];
				}
			}
		}
	}

	public function user_login($username, $password)
	{
		global $db, $_SESSION;

		include_once INCDIR . 'PasswordHash.php';
		$phpass = new PasswordHash(8, false);
		$query = "SELECT user_id, user_hash, user_pass FROM users WHERE user_name = :user_name";
		$params = array();
		$params[] = array(':user_name', $username, 'str');
		$db->query($query, $params);
		$user_data = $db->result();
		if ($phpass->CheckPassword($password, $user_data['user_pass']))
		{
			// generate a random unguessable token
			$_SESSION['csrftoken'] = generateToken();

			$_SESSION['TRACK_LOGGED_IN'] 		= $user_data['user_id'];
			$_SESSION['TRACK_LOGGED_NUMBER'] 	= strspn($user_data['user_pass'], $user_data['user_hash']);
			$_SESSION['TRACK_LOGGED_PASS'] 		= $user_data['user_pass'];

			// Update "last login" fields in users table
			$query = "UPDATE users SET user_lastlogin = :date WHERE user_id = :user_id";
			$params = array();
			$params[] = array(':date', gmdate("Y-m-d"), 'str');
			$params[] = array(':user_id', $user_data['user_id'], 'int');
			$db->query($query, $params);
			return true;
		}
		else
		{
			return false;
		}
	}

	public function is_logged_in()
	{
		global $_SESSION, $_POST;

		if(isset($_SESSION['csrftoken']))
		{
			# Token should exist as soon as a user is logged in
			if(1 < count($_POST))		# More than 2 parameters in a POST (csrftoken + 1 more) => check
				$valid_req = ($_POST['csrftoken'] == $_SESSION['csrftoken']);
			else
				$valid_req = true;		# Neither GET nor POST params exist => permit
			if(!$valid_req) 
            {
				exit; // kill the page 
            }
		}
		return $this->logged_in;
	}

	public function is_valid_user($user_id) 
    { 
        global $db;

        $query = "SELECT user_id FROM users WHERE user_id = :user_id";
		$params = array(
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
        if ($db->numrows() == 0)
        {
            return false;
        }
		return true;
    }

	public function is_following($user_id) 
    { 
        global $db;

        $query = "SELECT follow_id FROM user_follows WHERE user_id = :user_id AND follow_user_id = :follow_user_id";
		$params = array(
			array(':user_id', $this->user_id, 'int'),
			array(':follow_user_id', $user_id, 'int'),
		);
		$db->query($query, $params);
        if ($db->numrows() == 0)
        {
            return false;
        }
		return true;
    }

	public function get_user_data($user_id) 
    { 
        global $db;

        $query = "SELECT * FROM users WHERE user_id = :user_id";
		$params = array(
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);

		return $db->result();
    }

	public function deletefollower($user_id) 
    { 
        global $db;

        $query = "DELETE FROM user_follows WHERE user_id = :user_id AND follow_user_id = :follow_user_id";
		$params = array(
			array(':user_id', $this->user_id, 'int'),
			array(':follow_user_id', $user_id, 'int'),
		);
		$db->query($query, $params);
    }

	public function addfollower($user_id) 
    { 
        global $db;

        $query = "INSERT INTO user_follows VALUES (NULL, :user_id, :follow_user_id, :date)";
		$params = array(
			array(':user_id', $this->user_id, 'int'),
			array(':follow_user_id', $user_id, 'int'),
			array(':date', date("Y-m-d"), 'str'),
		);
		$db->query($query, $params);
    }
}
?>
