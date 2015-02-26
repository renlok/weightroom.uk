<?php
class log
{
	public function get_log_data($user_id, $date)
	{
		global $db;
		$query = "SELECT i.*, ex.exercise_name, lx.logex_volume, lx.logex_reps, lx.logex_sets, lx.logex_comment, l.log_weight FROM logs As l
				LEFT JOIN log_items As i ON (l.log_id = i.log_id)
				LEFT JOIN exercises ex ON (ex.exercise_id = i.exercise_id)
				LEFT JOIN log_exercises As lx ON (lx.exercise_id = ex.exercise_id)
				WHERE l.log_date = :log_date AND l.user_id = :user_id";
		$params = array(
			array(':log_date', $date, 'str'),
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

	public function is_valid_log($user_id, $log_date)
	{
        global $db;
<<<<<<< HEAD
  
		$query = "SELECT log_id FROM logs WHERE user_id = :user_id AND log_date = :log_date";
		$params = array(
			array(':user_id', $user_id, 'int'),
=======

		$query = "SELECT log_id FROM logs WHERE user_id = :user_id AND log_date = :log_date";
		$params = array(
			array(':log_id', $log_id, 'int'),
>>>>>>> origin/master
			array(':log_date', $log_date, 'str')
		);
		$db->query($query, $params);
        if ($db->numrows() == 0)
        {
            return false;
        }
		return true;
	}

	public function load_log($user_id, $log_date, $results = '*')
	{
		global $db, $user;

		$query = "SELECT $results FROM logs WHERE user_id = :user_id AND log_date = :log_date";
		$params = array(
<<<<<<< HEAD
			array(':user_id', $user_id, 'int'),
=======
			array(':log_id', $log_id, 'int'),
>>>>>>> origin/master
			array(':log_date', $log_date, 'str')
		);
		$db->query($query, $params);
		return $db->result();
	}
	
	public function parse_new_log($log)
	{
		global $db, $user;
		// woop
		$log_data = array();
		$bodyweight = $user->user_data['user_weight'];
		$units = $user->user_data['user_unit']; // 1 = kg, 2 = lb
		$log_data['comment'] = '';
		$log_lines = explode("\n", $log);

		$exercise = '';
		foreach ($log_lines as $line)
		{
			// check if new exercise
			if ($line[0] == '#')
			{
				$exercise = substr($line, 1); // set exercise marker
				if (!isset($log_data[$exercise]))
					$log_data[$exercise] = array('name' => $exercise,
												'comment' => '',
												'sets' => array()); // create entry to array
				
				continue; // end this loop
			}
			
			// no exercise yet
			if ($exercise == '')
			{
				if (!empty($log_data['comment']))
				{
					$log_data['comment'] .= '<br>';
				}
				$log_data['comment'] .= $line;
				continue; // end this loop
			}

			// set details of excersice
			if (is_numeric($line[0])) // using weight
			{
				if (preg_match("/^([0-9]+)\s*(lb|kg)*/", $line, $matches)) // 1 = weight, 2 = lb/kg
				{
					// clear the weight from the line
					$line = str_replace($matches[0], '', $line);
					// check if units were used
					if (isset($matches[2]))
						$weight = $this->correct_weight($matches[1], $matches[2], $units);
					else
						$weight = $matches[1];
					// add the data to the array
					$log_data[$exercise]['sets'][] = $this->get_reps($exercise, $weight, $line);
				}
			}
			elseif ($line[0] == 'B' && $line[1] == 'W') // using bodyweight
			{
				if (preg_match("/^BW(\+|-)*\s*([0-9]+)*\s*(kg|lb)*/", $line, $matches)) // 1= +/- 2= weight, 3= lb/kg
				{
					// clear the weight from the line
					$line = str_replace($matches[0], '', $line);
					// check if units were used
					if ($matches[0] == 'BW')
					{
						$weight = $bodyweight;
					}
					else
					{
						if (isset($matches[3]))
							$correct_weight = $this->correct_weight($matches[2], $matches[3], $units);
						else
							$correct_weight = $matches[2];
						// was it + or - BW
						if ($matches[1] == '+')
							$weight = $bodyweight + $correct_weight;
						else
							$weight = $bodyweight - $correct_weight;
					}
					// add the data to the array
					$log_data[$exercise]['sets'][] = $this->get_reps($exercise, $weight, $line);
				}
			}
			else
			{
				if (strlen($log_data[$exercise]['comment']) > 1)
				{
					$log_data[$exercise]['comment'] .= '<br>';
				}
				$log_data[$exercise]['comment'] .= $line;
			}
			// /^BW(\+|-)*\s*([0-9]+)*\s*(kg|lb)*/g // matches BW+10kg
			// /^([0-9]+)\s*(lb|kg)*/g      matches weight given
		}

		return $log_data;
	}
	
	private function correct_weight($weight, $unit_used, $unit_want) // $unit_used = kg/lb $unit_want = 1/2
	{
		if (($unit_used == 'kg' && $unit_want == 1) || ($unit_used == 'lb' && $unit_want == 2))
		{
			return $weight;
		}
		elseif ($unit_used == 'kg' && $unit_want == 2)
		{
			return ($weight * 2.20462); // convert to lb
		}
		elseif ($unit_used == 'lb' && $unit_want == 1)
		{
			return ($weight * 0.453592); // convert to kg
		}
		else
		{
			return $weight;
		}
	}

	// where the magic happens
	private function get_reps($exercise, $weight, $line)
	{
		// set the variables
		$reps = '';
		$sets = '';
		$reps_given = false;
		$waiting_for_reps = false;
		$sets_given = false;
		$waiting_for_sets = false;
		$sets_array = array();
		$cleanline = str_replace(' ', '', $line);
		$spacescount = 0;
		$lettercount = 0;
		$string_array = str_split($line);
		foreach ($string_array as $chr)
		{
			$lettercount++;
			// skip blank spaces
			if ($chr == ' ')
			{
				$spacescount++;
				continue;
			}
			// end of sets just add the comment
			if (!is_numeric($chr) && !($chr == 'x' || $chr == 'X' || $chr == ','))
			{
				break;
			}
			// x detected and no reps have been set yet, set it to wait for reps input
			if (($chr == 'x' || $chr == 'X') && !$reps_given && !$waiting_for_reps)
			{
				$waiting_for_reps = true;
				continue;
			}
			// add reps
			if (is_numeric($chr) && !$reps_given && $waiting_for_reps)
			{
				$reps .= $chr;
				$chrnum = $lettercount - $spacescount;
				$nextchar = substr($cleanline, $chrnum, 1);
				if (!is_numeric($nextchar))
				{
					$reps_given = true;
				}
				continue;
			}
			// x detected, reps have been set but no sets have been, set it to wait for reps input
			if (($chr == 'x' || $chr == 'X') && $reps_given && !$sets_given && !$waiting_for_sets)
			{
				$waiting_for_sets = true;
				continue;
			}
			// add sets
			if (is_numeric($chr) && !$sets_given && $waiting_for_sets)
			{
				$sets .= $chr;
				$chrnum = $lettercount - $spacescount;
				$nextchar = substr($cleanline, $chrnum, 1);
				if (!is_numeric($nextchar))
				{
					$sets_given = true;
				}
				continue;
			}
			// comma format
			if ($chr == ',' && $reps_given)
			{
				// check theres a number after
				$chrnum = $lettercount - $spacescount;
				$nextchar = substr($cleanline, $chrnum, 1);
				if (is_numeric($nextchar))
				{
					$reps .= $chr;
					$reps_given = false;
				}
			}
		}
		$line = substr($line, ($lettercount - 1));
		return array('weight' => $weight,
					'reps' => ($reps == '') ? 1 : $reps,
					'sets' => ($sets == '') ? 1 : $sets,
					'line' => $line,);
	}

	public function store_new_log_data($log_data, $log_text, $log_date, $user_id, $user_weight)
	{
		global $db;
		// clear old entries
		$query = "DELETE FROM log_exercises WHERE logex_date = :log_date AND user_id = :user_id";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		$query = "DELETE FROM log_items WHERE logitem_date = :log_date AND user_id = :user_id";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);

		//check if its new
		if ($this->is_valid_log($user_id, $log_date))
		{
			// update log entry
<<<<<<< HEAD
			$query = "UPDATE logs SET log_text = :log_text, log_comment = :log_comment, log_weight = :log_weight WHERE log_date = :log_date AND user_id = :user_id";
=======
			$query = "UPDATE log SET log_text = :log_text, log_comment = :log_comment, log_weight = :log_weight WHERE logitem_date = :log_date AND user_id = :user_id";
>>>>>>> origin/master
			$params = array(
				array(':log_text', $log_text, 'str'),
				array(':log_comment', $log_data['comment'], 'str'),
				array(':log_weight', $user_weight, 'float'),
				array(':log_date', $log_date, 'str'),
				array(':user_id', $user_id, 'int')
			);
			$db->query($query, $params);
		}
		else
		{
			// add a new entry
<<<<<<< HEAD
			$query = "INSERT INTO logs (log_text, log_comment, log_weight, log_date, user_id) VALUES (:log_text, :log_comment, :log_weight, :log_date, :user_id)";
=======
			$query = "INSERT INTO log (log_text, log_comment, log_weight, logitem_date, user_id) VALUES (:log_text, :log_comment, :log_weight, :log_date, :user_id)";
>>>>>>> origin/master
			$params = array(
				array(':log_text', $log_text, 'str'),
				array(':log_comment', $log_data['comment'], 'str'),
				array(':log_weight', $user_weight, 'float'),
				array(':log_date', $log_date, 'str'),
				array(':user_id', $user_id, 'int')
			);
			$db->query($query, $params);
		}
		// todays log then update weight
		if ($log_date == date("Y-m-d"))
		{
			$query = "UPDATE users SET user_weight = :log_weight WHERE user_id = :user_id";
			$params = array(
				array(':log_weight', $user_weight, 'float'),
				array(':user_id', $user_id, 'int')
			);
			$db->query($query, $params);
		}

		$log_id = $this->load_log($user_id, $log_date, 'log_id');
<<<<<<< HEAD
		$log_id = $log_id['log_id'];
=======
>>>>>>> origin/master
		// add all of the exersice details
		foreach ($log_data as $exercise => $item)
		{
			// ignore the comment
<<<<<<< HEAD
			if (isset($item['sets']))
			{
				// reset totals
				$total_volume = $total_reps = $total_sets = 0;
				$exercise_id = $this->get_exercise_id($user_id, $exercise);
=======
			if ($exercise != 'comment')
			{
				// reset totals
				$total_volume = $total_reps = $total_sets = 0;
				$exercise_id = $log->get_exercise_id($user_id, $exercise);
>>>>>>> origin/master
				$prs = $this->get_prs($user_id, $log_date, $exercise);
				foreach ($item['sets'] as $set)
				{
					$total_volume += ($set['weight'] * $set['reps'] * $set['sets']);
					$total_reps += $set['reps'];
					$total_sets += $set['sets'];
					// insert into log_items
					$query = "INSERT INTO log_items (logitem_date, log_id, user_id, exercise_id, logitem_weight, logitem_reps, logitem_sets, logitem_comment)
								VALUES (:logitem_date, :log_id, :user_id, :exercise_id, :logitem_weight, :logitem_reps, :logitem_sets, :logitem_comment)";
					$params = array(
						array(':logitem_date', $log_date, 'str'),
						array(':log_id', $log_id, 'int'),
						array(':user_id', $user_id, 'int'),
						array(':exercise_id', $exercise_id, 'int'),
						array(':logitem_weight', $set['weight'], 'float'),
						array(':logitem_reps', $set['reps'], 'int'),
						array(':logitem_sets', $set['sets'], 'int'),
<<<<<<< HEAD
						array(':logitem_comment', $set['line'], 'str'),
=======
						array(':logitem_comment', $set['comment'], 'str'),
>>>>>>> origin/master
					);
					$db->query($query, $params);
					// check its a pr
					if ($prs[$set['reps']] < $set['weight'])
					{
						// new pr !!
						$this->update_prs($user_id, $log_date, $exercise_id, $set['weight'], $set['reps']);
					}
				}
				// insert into log_exercises 
				$query = "INSERT INTO log_exercises (logex_date, log_id, user_id, exercise_id, logex_volume, logex_reps, logex_sets, logex_comment)
						VALUES (:logex_date, :log_id, :user_id, :exercise_id, :logex_volume, :logex_reps, :logex_sets, :logex_comment)";
				$params = array(
					array(':logex_date', $log_date, 'str'),
					array(':log_id', $log_id, 'int'),
					array(':user_id', $user_id, 'int'),
					array(':exercise_id', $exercise_id, 'int'),
					array(':logex_volume', $total_volume, 'float'),
					array(':logex_reps', $total_reps, 'int'),
					array(':logex_sets', $total_sets, 'int'),
					array(':logex_comment', $item['comment'], 'str'),
				);
				$db->query($query, $params);
			}
		}
	}

	private function get_exercise_id($user_id, $exercise_name)
	{
		global $db;

		$query = "SELECT exercise_id FROM exercises WHERE user_id = :user_id AND exercise_name = :exercise_name";
		$params = array(
			array(':exercise_name', $exercise_name, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		if ($db->numrows() > 0)
		{
			// already exists then return old id
			return $db->result('exercise_id');
		}
		else
		{
			// insert the exercise
			$query = "INSERT INTO exercises (user_id, exercise_name) VALUES (:user_id, :exercise_name)";
			$params = array(
				array(':exercise_name', $exercise_name, 'str'),
				array(':user_id', $user_id, 'int')
			);
			$db->query($query, $params);
			// return the new id
			return $db->lastInsertId();
		}
	}

	// load the pr of the given exercise on a given day for each rep range
	private function get_prs($user_id, $log_date, $exercise_name)
	{
		global $db;
		// load all preceeding prs
		$query = "SELECT MAX(pr_weight) as pr_weight, pr_reps FROM exercise_records pr
				LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
				WHERE pr.user_id = :user_id AND e.exercise_name = :exercise_name
				AND pr_date < :log_date
				GROUP BY pr_reps";
		$params = array(
			array(':exercise_name', $exercise_name, 'str'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		$prs = array();
		while ($row = $db->fetch())
		{
			$prs[$row['pr_reps']] = $row['pr_weight'];
		}
		return $prs;
	}

	// the user has set a pr we need to add/update it in the database
	private function update_prs($user_id, $log_date, $exercise_id, $set_weight, $set_reps)
	{
		global $db;
		// is there an exsiting pr set on that day?
		$query = "SELECT pr_id FROM exercise_records WHERE user_id = :user_id AND pr_date = :log_date AND exercise_id = :exercise_id AND pr_reps = :pr_reps";
		$params = array(
			array(':exercise_id', $exercise_id, 'int'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int'),
			array(':pr_reps', $set_reps, 'int')
		);
		$db->query($query, $params);
		// check if it needs a new entry
		if ($db->numrows() == 0)
		{
			// insert new entry
			$query = "INSERT INTO exercise_records (exercise_id, user_id, pr_date, pr_weight, pr_reps)
					VALUES (:exercise_id, :user_id, :pr_date, :pr_weight, :pr_reps)";
			$params = array(
				array(':exercise_id', $exercise_id, 'int'),
				array(':user_id', $user_id, 'int'),
				array(':pr_date', $log_date, 'str'),
				array(':pr_weight', $set_weight, 'float'),
				array(':pr_reps', $set_reps, 'int')
			);
			$db->query($query, $params);
		}
		else
		{
			// update old entry
			$query = "UPDATE exercise_records SET pr_weight = :pr_weight
					WHERE exercise_id = :exercise_id AND user_id = :user_id AND pr_date = :pr_date AND pr_reps = :pr_reps";
			$params = array(
				array(':exercise_id', $exercise_id, 'int'),
				array(':user_id', $user_id, 'int'),
				array(':pr_date', $log_date, 'str'),
				array(':pr_weight', $set_weight, 'float'),
				array(':pr_reps', $set_reps, 'int')
			);
			$db->query($query, $params);
		}
	}
}
?>
