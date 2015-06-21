<?php
class dash {
	public function get_dash_data ($user_id, $page = 1)
	{
		global $db;
		$limit = 50;
		$offset = ($page - 1) * $limit;
		/*
		// get the logs posted by users you follow
		$query = "SELECT l.log_date, u.user_name, u.user_id FROM logs l
					LEFT JOIN user_follows f ON (l.user_id = f.follow_user_id)
					LEFT JOIN users u ON (u.user_id = f.follow_user_id)
					WHERE f.user_id = :user_id
					ORDER BY l.log_date DESC
					LIMIT :offset, :limit";
		*/
		// get the log you follow plus all comments on your logs
		$query = "(
				SELECT l.log_date, l.log_date As true_log_date, u.user_name, u.user_id, 0 As receiver_user_id, 'log' As type FROM logs l
				LEFT JOIN user_follows f ON (l.user_id = f.follow_user_id)
				LEFT JOIN users u ON (u.user_id = f.follow_user_id)
				WHERE f.user_id = :user_id
			)
			UNION
			(
				SELECT comment_date As log_date, log_date As true_log_date, u.user_name, u.user_id, c.receiver_user_id, 'comment' As type FROM log_comments c
				LEFT JOIN users u ON (u.user_id = c.sender_user_id)
				WHERE c.receiver_user_id = :receiver_user_id
				AND c.sender_user_id != :sender_user_id
			)
			UNION
			(
				SELECT c.comment_date As log_date, c.log_date As true_log_date, u.user_name, u.user_id, c.receiver_user_id, 'reply' As type FROM log_comments c
				LEFT JOIN users u ON (u.user_id = c.sender_user_id)
				LEFT JOIN log_comments p ON (p.parent_id = c.comment_id)
				WHERE p.sender_user_id = :parent_user_id AND c.sender_user_id != :sender_user_id
			)
			ORDER BY log_date DESC
			LIMIT :offset, :limit";
		$params = array();
		$params[] = array(':user_id', $user_id, 'int');
		$params[] = array(':receiver_user_id', $user_id, 'int');
		$params[] = array(':sender_user_id', $user_id, 'int');
		$params[] = array(':parent_user_id', $user_id, 'int');
		$params[] = array(':offset', $offset, 'int');
		$params[] = array(':limit', $limit, 'int');
		$db->query($query, $params);
		$data = array();
		$today = date('Y-m-d');
		$date1 = new DateTime($today);
		while ($row = $db->fetch())
		{
			// log post or comment
			$row['is_log'] = ($row['log_date'] == $row['true_log_date'] . ' 00:00:00');
			if ($row['is_log'])
				$posted_date = $row['true_log_date'];
			else
				$posted_date = substr($row['log_date'], 0, 10);
			// posted same day
			if ($today == $posted_date)
			{
				$row['posted'] = 'today';
			}
			else
			{
				$date2 = new DateTime($posted_date);
				$interval = $date1->diff($date2);
				if ($interval->y) { $row['posted'] = $interval->format("%y years ago"); }
				elseif ($interval->m) { $row['posted'] = $interval->format("%m months ago"); }
				elseif ($interval->d) { $row['posted'] = $interval->format("%d days ago"); }
			}
			$data[] = $row;
		}
		return $data;
	}
}
?>