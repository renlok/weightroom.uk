<?php
class log
{
	public function get_log_data($user_id, $date)
	{
		global $db;
		$query = "SELECT i.*, ex.exercise_name, lx.logex_volume, lx.logex_reps, lx.logex_sets, lx.logex_comment, l.log_weight FROM log As l
				LEFT JOIN exercises ex ON (ex.exercise_id = i.exercise_id)
				LEFT JOIN log_exercises As lx ON (l.log_id = ex.log_id)
				LEFT JOIN log_items As i ON (l.log_id = i.log_id)
				WHERE l.log_date = :log_date AND l.user_id = :user_id";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);

		// setup vars
		$data = array();
		$exercise = '';
		$weight = '';
		while($item = $db->fetch())
		{
			if ($weight != $item['log_weight'])
				$weight = $item['log_weight'];
			if ($exercise != $item['exercise_name'])
			{
				$exercise = $item['exercise_name'];
				$data[$exercise] = array(
					'total_volume' => $item['logex_volume'],
					'total_reps' => $item['logex_reps'],
					'total_sets' => $item['logex_sets'],
					'comment' => $item['logex_comment'],
					'sets' => array(),
				);
			}
			$data[$exercise]['sets'][] = array(
				'weight' => $item['logitem_weight'],
				'reps' => $item['logitem_reps'],
				'sets' => $item['logitem_sets'],
				'comment' => $item['logitem_comment'],
			);
		}

		return $data;
	}
}
?>