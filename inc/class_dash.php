<?php
class dash {
	public function get_dash_data ($user_id, $page = 1)
	{
		global $db;
		$limit = 50;
		$offset = ($page - 1) * $limit;
		$query = "SELECT l.log_date, u.user_name, u.user_id FROM logs l
					LEFT JOIN user_follows f ON (l.user_id = f.follow_user_id)
					LEFT JOIN users u ON (u.user_id = f.follow_user_id)
					WHERE f.user_id = :user_id
					ORDER BY l.log_date DESC
					LIMIT :offset, :limit";
		$params = array();
		$params[] = array(':user_id', $user_id, 'int');
		$params[] = array(':offset', $offset, 'int');
		$params[] = array(':limit', $limit, 'int');
		$db->query($query, $params);
		$data = array();
		$today = date('Y-m-d');
		$date1 = new DateTime($today);
		while ($row = $db->fetch())
		{
			// posted same day
			if ($today == $row['log_date'])
			{
				$row['posted'] = 'today';
			}
			else
			{
				$date2 = new DateTime($row['log_date']);
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