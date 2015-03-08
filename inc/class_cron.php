<?php
class cron
{
	public function fix_prs()
	{
		global $db;
		//prepare everything
		$query = "TRUNCATE exercise_records";
		$db->direct_query($query);
		$query = "UPDATE log_items SET is_pr = 0";
		$db->direct_query($query);
		// load the exercises
		$query = "SELECT exercise_id, user_id FROM exercises ORDER BY exercise_id ASC";
		$db->direct_query($query);
		$data = $db->fetchall();
		foreach($data as $row)
		{
			$query = "SELECT logitem_id, logitem_weight, logitem_reps, logitem_date FROM log_items WHERE exercise_id = :exercise_id ORDER BY logitem_date ASC";
			$params = array(
				array(':exercise_id', $row['exercise_id'], 'int')
			);
			$db->query($query, $params);
			$pr = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
			while($ex_data = $db->fetch())
			{
				if ($ex_data['logitem_reps'] <= 10 && $ex_data['logitem_reps'] > 0 && $pr[$ex_data['logitem_reps']] < $ex_data['logitem_weight'])
				{
					$pr[$ex_data['logitem_reps']] = $ex_data['logitem_weight'];
					$query = "UPDATE log_items SET is_pr = 1 WHERE logitem_id = :logitem_id";
					$params = array(
						array(':logitem_id', $ex_data['logitem_id'], 'int')
					);
					$db->query($query, $params);
					$query = "INSERT INTO exercise_records (exercise_id, user_id, pr_date, pr_weight, pr_reps)
							VALUES (:exercise_id, :user_id, :pr_date, :pr_weight, :pr_reps)";
					$params = array(
						array(':exercise_id', $row['exercise_id'], 'int'),
						array(':user_id', $row['user_id'], 'int'),
						array(':pr_date', $ex_data['logitem_date'], 'str'),
						array(':pr_weight', $ex_data['logitem_weight'], 'float'),
						array(':pr_reps', $ex_data['logitem_reps'], 'int')
					);
					$db->query($query, $params);
				}
			}
		}
	}

	public function fix_prs_with_id($exercise_id)
	{
		global $db;
		//prepare everything
		$query = "DELETE FROM exercise_records WHERE exercise_id = :exercise_id";
		$params = array(
			array(':exercise_id', $exercise_id, 'int')
		);
		$db->query($query, $params);
		$query = "UPDATE log_items SET is_pr = 0 WHERE exercise_id = :exercise_id";
		$params = array(
			array(':exercise_id', $exercise_id, 'int')
		);
		$db->query($query, $params);

		$query = "SELECT logitem_id, logitem_weight, logitem_reps, logitem_date, user_id FROM log_items WHERE exercise_id = :exercise_id ORDER BY logitem_date ASC";
		$params = array(
			array(':exercise_id', $exercise_id, 'int')
		);
		$db->query($query, $params);
		$pr = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
		while($ex_data = $db->fetch())
		{
			if ($ex_data['logitem_reps'] <= 10 && $ex_data['logitem_reps'] > 0 && $pr[$ex_data['logitem_reps']] < $ex_data['logitem_weight'])
			{
				$pr[$ex_data['logitem_reps']] = $ex_data['logitem_weight'];
				$query = "UPDATE log_items SET is_pr = 1 WHERE logitem_id = :logitem_id";
				$params = array(
					array(':logitem_id', $ex_data['logitem_id'], 'int')
				);
				$db->query($query, $params);
				$query = "INSERT INTO exercise_records (exercise_id, user_id, pr_date, pr_weight, pr_reps)
						VALUES (:exercise_id, :user_id, :pr_date, :pr_weight, :pr_reps)";
				$params = array(
					array(':exercise_id', $exercise_id, 'int'),
					array(':user_id', $ex_data['user_id'], 'int'),
					array(':pr_date', $ex_data['logitem_date'], 'str'),
					array(':pr_weight', $ex_data['logitem_weight'], 'float'),
					array(':pr_reps', $ex_data['logitem_reps'], 'int')
				);
				$db->query($query, $params);
			}
		}
	}
}
?>