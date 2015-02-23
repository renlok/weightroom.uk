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

	public function is_valid_log($user_id, $log_date)
	{
        global $db;

		$query = "SELECT log_id FROM logs WHERE user_id = :user_id AND log_date = :log_date";
		$params = array(
			array(':log_id', $log_id, 'int'),
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
			array(':log_id', $log_id, 'int'),
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

	private function store_new_log_data($log_data, $log_text, $log_date, $user_id, $user_weight)
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
			$query = "UPDATE log SET log_text = :log_text, log_comment = :log_comment, log_weight = :log_weight WHERE logitem_date = :log_date AND user_id = :user_id";
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
			$query = "INSERT INTO log (log_text, log_comment, log_weight, logitem_date, user_id) VALUES (:log_text, :log_comment, :log_weight, :log_date, :user_id)";
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
		// add all of the exersice details
		foreach ($log_data as $exercise => $item)
		{
			// ignore the comment
			if ($exercise != 'comment')
			{
				// reset totals
				$total_volume = $total_reps = $total_sets = 0;
				foreach ($item['sets'] as $set)
				{
					$total_volume += ($set['weight'] * $set['reps'] * $set['sets']);
					$total_reps += $set['reps'];
					$total_sets += $set['sets'];
					// insert into log_items
					$query = "INSERT INTO log_items () VALUES ()";
				}
				// insert into log_exercises 
				$query = "INSERT INTO log_exercises () VALUES ()";
			}
		}
	}
}
?>