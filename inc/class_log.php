<?php
class log
{
	public function get_log_data($user_id, $date)
	{
		global $db, $user;
		$query = "SELECT i.*, ex.exercise_name, lx.logex_volume, lx.logex_reps, lx.logex_sets, lx.logex_comment, lx.logex_order
				FROM log_items As i
				LEFT JOIN exercises ex ON (ex.exercise_id = i.exercise_id)
				LEFT JOIN log_exercises As lx ON (lx.exercise_id = ex.exercise_id AND lx.log_id = i.log_id)
				WHERE i.logitem_date = :log_date AND i.user_id = :user_id
				ORDER BY logex_order ASC, logitem_order ASC";
		$params = array(
			array(':log_date', $date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		$log_data = $db->fetchall();

		// setup vars
		$data = array();
		$logex_number = '';
		$exercisepointer = 0;
		for ($i = 0, $count = count($log_data); $i < $count; $i++)
		{
			$item = $log_data[$i];
			if ($logex_number != $item['logex_order'])
			{
				$logex_number = $item['logex_order'];
				$exercisepointer++;
				$data[$exercisepointer] = array(
					'exercise' => $item['exercise_name'],
					'total_volume' => correct_weight($item['logex_volume'], 'kg', $user->user_data['user_unit']),
					'total_reps' => $item['logex_reps'],
					'total_sets' => $item['logex_sets'],
					'comment' => $item['logex_comment'],
					'sets' => array(),
				);
			}
			$weight = correct_weight($item['logitem_weight'], 'kg', $user->user_data['user_unit']);
			// include your failed reps in the total volume
			if ($user->user_data['user_volumeincfails'] == 1 && $item['logitem_reps'] == 0)
			{
				// we are assuming the failed rep now counts as 1 as you still attempted it
				$data[$exercisepointer]['total_volume'] += $weight * $item['logitem_sets'];
			}
			$data[$exercisepointer]['sets'][] = array(
				'logitem_weight' => $weight,
				'logitem_reps' => $item['logitem_reps'],
				'logitem_sets' => $item['logitem_sets'],
				'logitem_rpes' => $item['logitem_rpes'],
				'logitem_comment' => $item['logitem_comment'],
				'est1rm' => correct_weight($item['logitem_1rm'], 'kg', $user->user_data['user_unit']),
				'is_pr' => $item['is_pr'],
				'is_bw' => $item['is_bw'],
			);
		}

		return $data;
	}

	public function is_valid_log($user_id, $log_date)
	{
        global $db;

		$query = "SELECT log_id FROM logs WHERE user_id = :user_id AND log_date = :log_date";
		$params = array(
			array(':user_id', $user_id, 'int'),
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
		global $db;

		$query = "SELECT $results FROM logs WHERE user_id = :user_id AND log_date = :log_date";
		$params = array(
			array(':user_id', $user_id, 'int'),
			array(':log_date', $log_date, 'str')
		);
		$db->query($query, $params);
		return $db->result();
	}

	public function load_log_list($user_id, $log_date)
	{
		global $db, $user;

		// get logs within +/- 40 days
		$first_day = strtotime($log_date . '-01 00:00:00');
		$query = "SELECT log_date FROM logs WHERE user_id = :user_id AND log_date >= :log_date_last AND log_date <= :log_date_next ORDER BY log_date ASC";
		$params = array(
			array(':user_id', $user_id, 'int'),
			array(':log_date_last', date("Y-m-d", $first_day), 'str'),
			array(':log_date_next', date("Y-m-d", strtotime("+1 month", $first_day)), 'str')
		);
		$db->query($query, $params);
		return $db->fetchall();
	}

	public function build_log_list($data)
	{
		$logs_data = '';
		$i = 0;
		$len = count($data);
		foreach ($data as $date)
		{
			if ($i == $len - 1)
			{
				$logs_data .= "\t\"{$date['log_date']}\"\n";
			}
			else
			{
				$logs_data .= "\t\"{$date['log_date']}\",\n";
			}
			$i++;
		}
		return $logs_data;
	}

	public function parse_new_log($log, $bodyweight)
	{
		global $db, $user;
		// woop
		$log_data = array();
		// $user->user_data['user_unit'] : 1 = kg, 2 = lb
		$units = 1; // stored units should always be in kg
		$log_data['comment'] = '';
		$log_lines = explode("\n", $log);

		$exercise = '';
		$position = 0; // a pointer for when each set was done (to keep the order)
		$exersiceposition = 0; // a second position pointer
		$exersicepointers = array(); // for if there is multiple groups of exercise sets
		foreach ($log_lines as $line)
		{
			// check if blank line
			if (strlen($line) == 0)
			{
				continue;
			}

			// check if new exercise
			if ($line[0] == '#')
			{
				$exercise = substr($line, 1); // set exercise marker
				if (isset($exersicepointers[$exercise]))
					$exersicepointers[$exercise]++;
				else
					$exersicepointers[$exercise] = 0;
				// add new exercise group to array
				$log_data[$exercise][$exersicepointers[$exercise]] = array(
						'name' => $exercise,
						'comment' => '',
						'position' => $exersiceposition,
						'sets' => array());
				$exersiceposition++;
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
				// match aa weight given
				if (preg_match("/^([0-9]+\.*[0-9]*)\s*(lbs?|kgs?)*/", $line, $matches)) // 1 = weight, 2 = lb/kg
				{
					// clear the weight from the line
					$line = str_replace($matches[0], '', $line);
					// check if units were used
					if (isset($matches[2]))
						$weight = correct_weight($matches[1], $matches[2], $units);
					elseif ($user->user_data['user_unit'] == 2)
						$weight = correct_weight($matches[1], 'lb', $units);
					else
						$weight = $matches[1];
					// add the data to the array
					$log_data[$exercise][$exersicepointers[$exercise]]['sets'][] = $this->get_reps($exercise, $weight, $line);
				}
			}
			elseif ($line[0] == 'B' && $line[1] == 'W') // using bodyweight
			{
				if (preg_match("/^BW(\+|-)*\s*([0-9]+\.*[0-9]*)*\s*(kgs?|lbs?)*/", $line, $matches)) // 1= +/- 2= weight, 3= lb/kg
				{
					// clear the weight from the line
					$line = str_replace($matches[0], '', $line);
					// check if units were used
					if ($matches[0] == 'BW')
					{
						$weight = 0;
					}
					else
					{
						if (isset($matches[3]))
							$correct_weight = correct_weight($matches[2], $matches[3], $units);
						elseif ($user->user_data['user_unit'] == 2)
							$correct_weight = correct_weight($matches[2], 'lb', $units);
						else
							$correct_weight = $matches[2];
						// was it + or - BW
						if ($matches[1] == '+')
							$weight = $correct_weight;
						else
							$weight = -1 * $correct_weight;
					}
					// add the data to the array
					$log_data[$exercise][$exersicepointers[$exercise]]['sets'][] = $this->get_reps($exercise, $weight, $line, true);
				}
			}
			else
			{
				if (strlen($log_data[$exercise][$exersicepointers[$exercise]]['comment']) > 1)
				{
					$log_data[$exercise][$exersicepointers[$exercise]]['comment'] .= '<br>';
				}
				$log_data[$exercise][$exersicepointers[$exercise]]['comment'] .= $line;
			}
			// /^BW(\+|-)*\s*([0-9]+)*\s*(kg|lb)*/g // matches BW+10kg
			// /^([0-9]+)\s*(lb|kg)*/g      matches weight given
		}

		return $log_data;
	}

	// where the magic happens
	private function get_reps($exercise, $weight, $line, $bw = false)
	{
		global $position;
		// set the variables
		$reps = '';
		$sets = '';
		$rpes = '';
		$reps_given = false;
		$waiting_for_reps = false;
		$comma_reps = false;
		$sets_given = false;
		$waiting_for_sets = false;
		$rpe_given = false;
		$waiting_for_rpes = false;
		$comma_rpes = false;
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
			if ((!is_numeric($chr) && !($chr == 'x' || $chr == 'X' || $chr == ',' || $chr == '@' || $chr == '.'))
				|| ($reps_given && $sets_given && $rpe_given))
			{
				$lettercount--;
				break;
			}
			// BEGIN REPS
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
				if (!is_numeric($nextchar) && $nextchar != ',')
				{
					$reps_given = true;
				}
				continue;
			}
			// comma format reps
			if ($chr == ',' && !$reps_given && $waiting_for_reps)
			{
				// check theres a number after
				$chrnum = $lettercount - $spacescount;
				$nextchar = substr($cleanline, $chrnum, 1);
				if (is_numeric($nextchar))
				{
					$reps .= $chr;
					$comma_reps = true;
				}
			}
			// BEGIN SETS
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
					//$sets = 1;
				}
				continue;
			}
			// BEGIN RPES
			// @ detected, set it to wait for rpe input
			if (($chr == '@') && !$waiting_for_rpes)
			{
				$waiting_for_rpes = true;
				continue;
			}
			// add rpe
			if ((is_numeric($chr) || $chr == '.') && !$rpe_given && $waiting_for_rpes)
			{
				$rpes .= $chr;
				$chrnum = $lettercount - $spacescount;
				$nextchar = substr($cleanline, $chrnum, 1);
				if (!(is_numeric($nextchar) || $nextchar == ',' || $nextchar == '.') && !$comma_rpes)
				{
					$rpe_given = true;
				}
				else if (floatval($rpes . $nextchar) > 10 && !$comma_rpes)
				{
					$rpe_given = true;
				}
				continue;
			}
			// comma format rpes
			if ($chr == ',' && !$rpe_given && $waiting_for_rpes)
			{
				// check theres a number after
				$chrnum = $lettercount - $spacescount;
				$nextchar = substr($cleanline, $chrnum, 1);
				if (is_numeric($nextchar))
				{
					$rpes .= $chr;
					$comma_rpes = true;
				}
			}
		}
		if ($comma_reps)
		{
			$sets = 1;
		}
		$line = substr($line, $lettercount);
		$setrep_data = array('weight' => $weight,
					'is_bw' => $bw,
					'reps' => ($reps == '') ? 1 : $reps,
					'sets' => ($sets == '') ? 1 : $sets,
					'rpes' => ($rpes == '') ? NULL : $rpes,
					'line' => trim($line),
					'position' => intval($position));
		$position++; // next position
		return $setrep_data;
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
		// clear out old pr data
		$query = "DELETE FROM exercise_records WHERE pr_date = :log_date AND user_id = :user_id";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);

		// delete log and exit function if no data
		if (strlen($log_text) == 0)
		{
			$query = "DELETE FROM logs WHERE log_date = :log_date AND user_id = :user_id";
			$params = array(
				array(':log_date', $log_date, 'str'),
				array(':user_id', $user_id, 'int')
			);
			$db->query($query, $params);
			return false;
		}

		//check if its new
		if ($this->is_valid_log($user_id, $log_date))
		{
			// update log entry
			$query = "UPDATE logs SET log_text = :log_text, log_comment = :log_comment, log_weight = :log_weight WHERE log_date = :log_date AND user_id = :user_id";
			$params = array(
				array(':log_text', $log_text, 'str'),
				array(':log_comment', $this->replace_video_urls($log_data['comment']), 'str'),
				array(':log_weight', $user_weight, 'float'),
				array(':log_date', $log_date, 'str'),
				array(':user_id', $user_id, 'int')
			);
			$db->query($query, $params);
		}
		else
		{
			// add a new entry
			$query = "INSERT INTO logs (log_text, log_comment, log_weight, log_date, user_id) VALUES (:log_text, :log_comment, :log_weight, :log_date, :user_id)";
			$params = array(
				array(':log_text', $log_text, 'str'),
				array(':log_comment', $this->replace_video_urls($log_data['comment']), 'str'),
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
		else
		{
			// update future logs
			$this->update_user_weights ($user_id, $log_date, $user_weight);
		}

		$log_id = $this->load_log($user_id, $log_date, 'log_id');
		$log_id = $log_id['log_id'];
		$new_prs = array();
		// add all of the exercise details
		foreach ($log_data as $exercise => $items)
		{
			// ignore the comment
			if (!($exercise == 'comment' && $items == $log_data['comment']))
			{
				// add a loop incase their are multiples of the same exercise
				for ($j = 0, $item_count = count($items); $j < $item_count; $j++)
				{
					// set $item for ease
					$item = $items[$j];
					// reset totals
					$total_volume = $total_reps = $total_sets = 0;
					$exercise_id = $this->get_exercise_id($user_id, $exercise);
					$prs = $this->get_prs($user_id, $log_date, $exercise);
					$max_estimate_rm = 0;
					foreach ($item['sets'] as $set)
					{
						$rep_arr = explode(',', $set['reps']);
						$rpe_arr = explode(',', $set['rpes']);
						$temp_sets = $set['sets'];
						for ($i = 0, $set_count = count($rep_arr); $i < $set_count; $i++)
						{
							// for comma format add the sets together
							if (isset($rep_arr[$i+1]) && $rep_arr[$i] == $rep_arr[$i+1])
							{
								$temp_sets++;
								continue;
							}
							$total_volume += ($set['weight'] * $rep_arr[$i] * $set['sets']);
							$total_reps += ($rep_arr[$i] * $set['sets']);
							$total_sets += $temp_sets;
							$is_pr = false;
							// check its a pr
							if ((!isset($prs[$rep_arr[$i]]) || floatval($prs[$rep_arr[$i]]) < floatval($set['weight'])) && $rep_arr[$i] != 0)
							{
								$is_pr = true;
								// new pr !!
								$this->update_prs($user_id, $log_date, $exercise_id, $set['weight'], $rep_arr[$i]);
								if (!isset($new_prs[$exercise]))
									$new_prs[$exercise] = array();
								$new_prs[$exercise][$rep_arr[$i]][] = $set['weight'];
								// update pr array
								$prs[$rep_arr[$i]] = $set['weight'];
							}
							$estimate_rm = $this->generate_rm($set['weight'], $rep_arr[$i]);
							// get estimate 1rm
							if ($max_estimate_rm < $estimate_rm)
							{
								$max_estimate_rm = $estimate_rm;
							}
							// insert into log_items
							$query = "INSERT INTO log_items (logitem_date, log_id, user_id, exercise_id, logitem_weight, logitem_reps, logitem_sets, logitem_rpes, logitem_comment, logitem_1rm, is_pr, is_bw, logitem_order)
										VALUES (:logitem_date, :log_id, :user_id, :exercise_id, :logitem_weight, :logitem_reps, :logitem_sets, :logitem_rpes, :logitem_comment, :logitem_rm, :is_pr, :is_bw, :logitem_order)";
							$params = array(
								array(':logitem_date', $log_date, 'str'),
								array(':log_id', $log_id, 'int'),
								array(':user_id', $user_id, 'int'),
								array(':exercise_id', $exercise_id, 'int'),
								array(':logitem_weight', $set['weight'], 'float'),
								array(':logitem_reps', $rep_arr[$i], 'int'),
								array(':logitem_sets', $temp_sets, 'int'),
								array(':logitem_comment', $set['line'], 'str'),
								array(':logitem_rm', $estimate_rm, 'float'),
								array(':is_pr', (($is_pr == false) ? 0 : 1), 'int'),
								array(':is_bw', (($set['is_bw'] == false) ? 0 : 1), 'int'),
								array(':logitem_order', $set['position'], 'int'),
							);
							if ($rpe_arr[$i] == NULL)
								$params[] = array(':logitem_rpes', NULL, 'int');
							else
								$params[] = array(':logitem_rpes', $rpe_arr[$i], 'float');
							$db->query($query, $params);
							$temp_sets = $set['sets'];
						}
					}
					// insert into log_exercises
					$query = "INSERT INTO log_exercises (logex_date, log_id, user_id, exercise_id, logex_volume, logex_reps, logex_sets, logex_1rm, logex_comment, logex_order)
							VALUES (:logex_date, :log_id, :user_id, :exercise_id, :logex_volume, :logex_reps, :logex_sets, :logex_rm, :logex_comment, :logex_order)";
					$params = array(
						array(':logex_date', $log_date, 'str'),
						array(':log_id', $log_id, 'int'),
						array(':user_id', $user_id, 'int'),
						array(':exercise_id', $exercise_id, 'int'),
						array(':logex_volume', $total_volume, 'float'),
						array(':logex_reps', $total_reps, 'int'),
						array(':logex_sets', $total_sets, 'int'),
						array(':logex_rm', $max_estimate_rm, 'float'),
						array(':logex_comment', $this->replace_video_urls($item['comment']), 'str'),
						array(':logex_order', $item['position'], 'int'),
					);
					$db->query($query, $params);
				}
			}
		}

		//return your new records :)
		return $new_prs;
	}

	public function rebuild_log_text($user_id, $log_date)
	{
		global $user, $db;
		// get the log data
		$log_data = $this->get_log_data($user_id, $log_date);
		$log_text = ''; // set that variable !!
		foreach ($log_data as $log_items)
		{
			$log_text .= "#" . ucwords($log_items['exercise']) . "\n"; // set exersice name
			foreach ($log_items['sets'] as $set)
			{
				// get user units
				$units = $user->get_user_data($user_id, 'user_unit');
				$unit_string = ($units['user_unit'] == 1) ? 'kg' : 'lb';
				if ($set['is_bw'] == 0)
				{
					$weight = $set['logitem_weight'] . ' ' . $unit_string;
				}
				else
				{
					if ($set['logitem_weight'] != 0)
					{
						$weight = 'BW' . $set['logitem_weight'] . ' ' . $unit_string;
					}
					else
					{
						$weight = 'BW';
					}
				}
				if (!empty($set['logitem_rpes']))
				{
					$pre = " @{$set['logitem_rpes']}";
				}
				else
				{
					$pre = '';
				}
				$log_text .= "$weight x {$set['logitem_reps']} x {$set['logitem_sets']}$pre " . trim($set['logitem_comment']) . "\n"; // add sets
			}
			if (strlen(trim($log_items['comment'])) > 0)
				$log_text .= "\n" . trim($log_items['comment']) . "\n"; // set comment
			$log_text .= "\n";
		}
		$log_text = rtrim($log_text);
		$query = "UPDATE logs SET log_text = :log_text, log_update_text = 0 WHERE log_date = :log_date AND user_id = :user_id";
		$params = array(
			array(':log_text', $log_text, 'str'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);

		return $log_text;
	}

	private function replace_video_urls($comment)
	{
		return preg_replace(
			"/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/im",
			"<iframe width=\"420\" height=\"315\" src=\"//www.youtube.com/embed/$2\" frameborder=\"0\" allowfullscreen></iframe>",
			$comment
		);
		//$width = '640';
		//$height = '385';
	}

	private function update_user_weights($user_id, $log_date, $user_weight)
	{
		global $db;

		// get old weight
		$query = "SELECT log_weight FROM logs WHERE log_date < :log_date AND user_id = :user_id ORDER BY log_date DESC LIMIT 1";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		$old_weight = $db->result('log_weight');

		// update log entries with new weight
		$query = "UPDATE logs SET log_weight = :log_weight WHERE log_date > :log_date AND user_id = :user_id AND log_weight = :old_weight";
		$params = array(
			array(':log_weight', $user_weight, 'float'),
			array(':old_weight', $old_weight, 'float'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
	}

	public function get_user_weight($user_id, $log_date)
	{
		global $db;

		// get old weight
		$query = "SELECT log_weight FROM logs WHERE log_date < :log_date AND user_id = :user_id ORDER BY log_date DESC LIMIT 1";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		$old_weight = $db->result('log_weight');
		return $old_weight;
	}

	public function get_exercise_id($user_id, $exercise_name)
	{
		global $db;

		$query = "SELECT exercise_id FROM exercises WHERE user_id = :user_id AND exercise_name = :exercise_name";
		$params = array(
			array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
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
				array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
				array(':user_id', $user_id, 'int')
			);
			$db->query($query, $params);
			// return the new id
			return $db->lastInsertId();
		}
	}

	public function is_valid_exercise($user_id, $exercise_name)
	{
		global $db;

		$query = "SELECT exercise_id FROM exercises WHERE user_id = :user_id AND exercise_name = :exercise_name";
		$params = array(
			array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		if ($db->numrows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function generate_rm($weight, $reps, $rm = 1)
	{
		if ($reps == $rm)
		{
			return $weight;
		}

		//for all reps > 1 calculate the 1RMs
		$lomonerm = $weight * pow($reps, 1 / 10);
		$brzonerm = $weight * (36 / (37 - $reps));
		$eplonerm = $weight * (1 + ($reps / 30));
		$mayonerm = ($weight * 100) / (52.2 + (41.9 * exp(-1 * ($reps * 0.055))));
		$ocoonerm = $weight * (1 + $reps * 0.025);
		$watonerm = ($weight * 100) / (48.8 + (53.8 * exp(-1 * ($reps * 0.075))));
		$lanonerm = $weight * 100 / (101.3 - 2.67123 * $reps);

		if ($rm == 1)
		{
			// get the average
			return ($lomonerm + $brzonerm + $eplonerm + $mayonerm + $ocoonerm + $watonerm + $lanonerm) / 7;
		}

		$lomrm = floor($lomonerm / (pow($rm, 1 / 10)));
		$brzrm = floor(($brzonerm * (37 - $rm)) / 36);
		$eplrm = floor($eplonerm / ((1 + ($rm / 30))));
		$mayrm = floor(($mayonerm * (52.2 + (41.9 * exp(-1 * ($rm * 0.055))))) / 100);
		$ocorm = floor(($ocoonerm / (1 + $rm * 0.025)));
		$watrm = floor(($watonerm * (48.8 + (53.8 * exp(-1 * ($rm * 0.075))))) / 100);
		$lanrm = floor((($lanonerm * (101.3 - 2.67123 * $rm)) / 100));

		// return the average value
		return floor(($lomrm + $brzrm + $eplrm + $mayrm + $ocorm + $watrm + $lanrm) / 7);
	}

	// load the pr of the given exercise on a given day for each rep range
	public function get_prs($user_id, $log_date, $exercise_name, $return_date = false)
	{
		global $db;
		// load all preceeding prs
		$pr_date = ($return_date) ? ', MAX(pr_date) as pr_date' : '';
		$query = "SELECT MAX(pr_weight) as pr_weight, pr_reps " . $pr_date . " FROM exercise_records pr
				LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
				WHERE pr.user_id = :user_id AND e.exercise_name = :exercise_name
				AND pr_date <= :log_date
				GROUP BY pr_reps";
		$params = array(
			array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		$prs = array();
		$date = array();
		while ($row = $db->fetch())
		{
			if ($return_date)
			{
				$date[$row['pr_reps']] = $row['pr_date'];
			}
			$prs[$row['pr_reps']] = $row['pr_weight'];
		}
		if ($return_date)
		{
			return array($prs, $date);
		}
		else
		{
			return $prs;
		}
	}

	// the user has set a pr we need to add/update it in the database
	private function update_prs($user_id, $log_date, $exercise_id, $set_weight, $set_reps)
	{
		global $db;

		// dont log reps over 10
		if ($set_reps > 10 || $set_reps < 1)
			return false;

		// insert new entry
		$query = "INSERT INTO exercise_records (exercise_id, user_id, pr_date, pr_weight, pr_reps, pr_1rm)
				VALUES (:exercise_id, :user_id, :pr_date, :pr_weight, :pr_reps, :pr_rm)";
		$params = array(
			array(':exercise_id', $exercise_id, 'int'),
			array(':user_id', $user_id, 'int'),
			array(':pr_date', $log_date, 'str'),
			array(':pr_weight', $set_weight, 'float'),
			array(':pr_reps', $set_reps, 'int'),
			array(':pr_rm', $this->generate_rm($set_weight, $set_reps), 'float'),
		);
		$db->query($query, $params);

		// delete future logs that have lower prs
		$query = "DELETE FROM exercise_records WHERE user_id = :user_id AND pr_date > :log_date AND exercise_id = :exercise_id AND pr_reps = :pr_reps AND pr_weight < :pr_weight";
		$params = array(
			array(':exercise_id', $exercise_id, 'int'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int'),
			array(':pr_reps', $set_reps, 'int'),
			array(':pr_weight', $set_weight, 'float')
		);
		$db->query($query, $params);
		$query = "UPDATE log_items SET is_pr = 0 WHERE user_id = :user_id AND logitem_date > :log_date AND exercise_id = :exercise_id AND logitem_reps = :pr_reps AND logitem_weight < :pr_weight";
		$params = array(
			array(':exercise_id', $exercise_id, 'int'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int'),
			array(':pr_reps', $set_reps, 'int'),
			array(':pr_weight', $set_weight, 'float')
		);
		$db->query($query, $params);

		// add past prs if needed
		$query = "SELECT log_id, logitem_weight FROM log_items WHERE user_id = :user_id AND logitem_date < :log_date AND exercise_id = :exercise_id AND logitem_reps = :pr_reps AND logitem_weight > :pr_weight AND is_pr = 0";
		$params = array(
			array(':exercise_id', $exercise_id, 'int'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int'),
			array(':pr_reps', $set_reps, 'int'),
			array(':pr_weight', $set_weight, 'float')
		);
		$db->query($query, $params);
		while ($row = $db->fetch())
		{
			// update is_pr flag
			$query = "UPDATE log_items SET is_pr = 1 WHERE log_id = :log_id";
			$params = array(
				array(':log_id', $row['log_id'], 'int')
			);
			$db->query($query, $params);
			// insert pr data
			$query = "INSERT INTO exercise_records (exercise_id, user_id, pr_date, pr_weight, pr_reps, pr_1rm)
					VALUES (:exercise_id, :user_id, :pr_date, :pr_weight, :pr_reps, :pr_rm)";
			$params = array(
				array(':exercise_id', $exercise_id, 'int'),
				array(':user_id', $user_id, 'int'),
				array(':pr_date', $log_date, 'str'),
				array(':pr_weight', $row['logitem_weight'], 'float'),
				array(':pr_reps', $set_reps, 'int'),
				array(':pr_rm', $this->generate_rm($row['logitem_weight'], $set_reps), 'float'),
			);
			$db->query($query, $params);
		}
	}

	public function get_bodyweight($user_id, $coefficient = '', $range = 0)
	{
		global $db, $user;
		$params = array();
		if ($coefficient == 'wilks')
		{
			$coefficient_sql_select = ', e.exercise_name, le.logitem_weight';
			$coefficient_sql_join = 'INNER JOIN log_items le ON (le.log_id = l.log_id)
				INNER JOIN exercises e ON (e.exercise_id = le.exercise_id)';
			$coefficient_sql = 'AND (le.exercise_id = :user_squatid OR le.exercise_id = :user_deadliftid OR le.exercise_id = :user_benchid) AND is_pr = 1';
			$params[] = array(':user_squatid', $user->user_data['user_squatid'], 'int');
			$params[] = array(':user_deadliftid', $user->user_data['user_deadliftid'], 'int');
			$params[] = array(':user_benchid', $user->user_data['user_benchid'], 'int');
		}
		elseif ($coefficient == 'sinclair')
		{
			$coefficient_sql_select = ', e.exercise_name, le.logitem_weight';
			$coefficient_sql_join = 'INNER JOIN log_items le ON (le.log_id = l.log_id)
				INNER JOIN exercises e ON (e.exercise_id = le.exercise_id)';
			$coefficient_sql = 'AND (le.exercise_id = :user_snatchid OR le.exercise_id = :user_cleanjerkid) AND is_pr = 1';
			$params[] = array(':user_snatchid', $user->user_data['user_snatchid'], 'int');
			$params[] = array(':user_cleanjerkid', $user->user_data['user_cleanjerkid'], 'int');
		}
		else
		{
			$coefficient_sql_select = '';
			$coefficient_sql_join = '';
			$coefficient_sql = ' AND log_weight != 0';
		}
		if ($range > 0)
		{
			$coefficient_sql .= ' AND log_date >= :pr_date';
			$params[] = array(':pr_date', date("Y-m-d", strtotime("-$range months")), 'str');
		}
		// load all bodyweight
		$query = "SELECT log_date, log_weight $coefficient_sql_select FROM logs l
				$coefficient_sql_join
				WHERE user_id = :user_id
				$coefficient_sql
				ORDER BY log_date ASC";
		$params[] = array(':user_id', $user_id, 'int');
		$db->query($query, $params);
		$return_array = array(
			'bodyweight' => array(),
		);
		$last_weight = 0; // so we can see when it changes
		$last_exercise = array();
		while ($row = $db->fetch())
		{
			// is it a new weight
			if ($last_weight != $row['log_weight'])
			{
				$return_array['bodyweight'][$row['log_date']] = $row['log_weight'];
				// set new weight
				$last_weight = $row['log_weight'];
			}
			// are we including exercises
			if ($coefficient != '')
			{
				if ($last_exercise[$row['exercise_name']] != $row['logitem_weight'])
				{
					$return_array[$row['exercise_name']][$row['log_date']] = $row['logitem_weight'];
					// set new weight
					$last_exercise[$row['exercise_name']] = $row['log_weight'];
				}
			}
		}
		if ($coefficient != '')
		{
			return $return_array;
		}
		else
		{
			return $return_array['bodyweight'];
		}
	}

	public function get_prs_data($user_id, $exercise_name, $range = 0)
	{
		global $db;
		if ($range > 0)
		{
			// load prs after x months ago
			$query = "SELECT pr_weight, pr_reps, pr_date, pr_1rm FROM exercise_records pr
					LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
					WHERE pr.user_id = :user_id AND e.exercise_name = :exercise_name AND pr_date >= :pr_date
					ORDER BY pr_date ASC";
			$params = array(
				array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
				array(':pr_date', date("Y-m-d", strtotime("-$range months")), 'str'),
				array(':user_id', $user_id, 'int')
			);
		}
		else
		{
			// load all prs
			$query = "SELECT pr_weight, pr_reps, pr_date, pr_1rm FROM exercise_records pr
					LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
					WHERE pr.user_id = :user_id AND e.exercise_name = :exercise_name
					ORDER BY pr_date ASC";
			$params = array(
				array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
				array(':user_id', $user_id, 'int')
			);
		}
		$db->query($query, $params);
		// set the order
		$prs = array(
			'Approx. 1' => array(),
			1 => array(),
			2 => array(),
			3 => array(),
			4 => array(),
			5 => array(),
			6 => array(),
			7 => array(),
			8 => array(),
			9 => array(),
			10 => array());
		$highest = 0; // for est 1rm so you only show the actual PRs
		while ($row = $db->fetch())
		{
			$prs[$row['pr_reps']][$row['pr_date']] = $row['pr_weight'];
			if ($highest < $row['pr_1rm'])
			{
				$highest = $prs['Approx. 1'][$row['pr_date']] = $row['pr_1rm'];
			}
		}
		// unset empty arrays
		if (count($prs[1]) == 0)
			unset($prs[1]);
		if (count($prs[2]) == 0)
			unset($prs[2]);
		if (count($prs[3]) == 0)
			unset($prs[3]);
		if (count($prs[4]) == 0)
			unset($prs[4]);
		if (count($prs[5]) == 0)
			unset($prs[5]);
		if (count($prs[6]) == 0)
			unset($prs[6]);
		if (count($prs[7]) == 0)
			unset($prs[7]);
		if (count($prs[8]) == 0)
			unset($prs[8]);
		if (count($prs[9]) == 0)
			unset($prs[9]);
		if (count($prs[10]) == 0)
			unset($prs[10]);
		return $prs;
	}

	public function get_prs_data_weekly($user_id, $exercise_name, $range = 0)
	{
		global $db;
		if ($range > 0)
		{
			// load prs after x months ago
			$query = "SELECT logitem_weight, logitem_reps, logitem_date FROM log_items
					WHERE (logitem_weight, logitem_reps, WEEK(logitem_date)) IN
					(
						SELECT MAX(logitem_weight) as logitem_weight, logitem_reps, WEEK(logitem_date)
						FROM log_items pr
						LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
						WHERE pr.user_id = :user_id AND e.exercise_name = :exercise_name AND pr.logitem_reps != 0 AND pr.logitem_reps <= 10 AND logitem_date >= :logitem_date
						GROUP BY logitem_reps, WEEK(logitem_date)
					)
					ORDER BY logitem_reps ASC , logitem_date ASC";
			$params = array(
				array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
				array(':logitem_date', date("Y-m-d", strtotime("-$range months")), 'str'),
				array(':user_id', $user_id, 'int')
			);
		}
		else
		{
			// load all prs
			$query = "SELECT logitem_weight, logitem_reps, logitem_date FROM log_items
					WHERE (logitem_weight, logitem_reps, WEEK(logitem_date)) IN
					(
						SELECT MAX(logitem_weight) as logitem_weight, logitem_reps, WEEK(logitem_date)
						FROM log_items pr
						LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
						WHERE pr.user_id = :user_id AND e.exercise_name = :exercise_name AND pr.logitem_reps != 0 AND pr.logitem_reps <= 10
						GROUP BY logitem_reps, WEEK(logitem_date)
					)
					ORDER BY logitem_reps ASC , logitem_date ASC";
			$params = array(
				array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
				array(':user_id', $user_id, 'int')
			);
		}
		$db->query($query, $params);
		$prs = array();
		while ($row = $db->fetch())
		{
			if (!isset($prs[$row['logitem_reps']]))
				$prs[$row['logitem_reps']] = array();
			$prs[$row['logitem_reps']][$row['logitem_date']] = $row['logitem_weight'];
		}
		return $prs;
	}

	public function get_prs_data_monthly($user_id, $exercise_name, $range = 0)
	{
		global $db;
		if ($range > 0)
		{
			// load prs after x months ago
			$query = "SELECT logitem_weight, logitem_reps, logitem_date FROM log_items
					WHERE (logitem_weight, logitem_reps, MONTH(logitem_date)) IN
					(
						SELECT MAX(logitem_weight) as logitem_weight, logitem_reps, MONTH(logitem_date)
						FROM log_items pr
						LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
						WHERE pr.user_id = :user_id AND e.exercise_name = :exercise_name AND pr.logitem_reps != 0 AND pr.logitem_reps <= 10 AND logitem_date >= :logitem_date
						GROUP BY logitem_reps, MONTH(logitem_date)
					)
					ORDER BY logitem_reps ASC , logitem_date ASC";
			$params = array(
				array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
				array(':logitem_date', date("Y-m-d", strtotime("-$range months")), 'str'),
				array(':user_id', $user_id, 'int')
			);
		}
		else
		{
			// load all prs
			$query = "SELECT logitem_weight, logitem_reps, logitem_date FROM log_items
					WHERE (logitem_weight, logitem_reps, MONTH(logitem_date)) IN
					(
						SELECT MAX(logitem_weight) as logitem_weight, logitem_reps, MONTH(logitem_date)
						FROM log_items pr
						LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
						WHERE pr.user_id = :user_id AND e.exercise_name = :exercise_name AND pr.logitem_reps != 0 AND pr.logitem_reps <= 10
						GROUP BY logitem_reps, MONTH(logitem_date)
					)
					ORDER BY logitem_reps ASC , logitem_date ASC";
			$params = array(
				array(':exercise_name', strtolower(trim($exercise_name)), 'str'),
				array(':user_id', $user_id, 'int')
			);
		}
		$db->query($query, $params);
		$prs = array();
		while ($row = $db->fetch())
		{
			if (!isset($prs[$row['logitem_reps']]))
				$prs[$row['logitem_reps']] = array();
			$prs[$row['logitem_reps']][$row['logitem_date']] = $row['logitem_weight'];
		}
		return $prs;
	}

	public function get_prs_data_compare($user_id, $reps, $ex_name1, $ex_name2, $ex_name3 = '', $ex_name4 = '', $ex_name5 = '')
	{
		global $db;
		$extra_sql = '';
		$params = array();
		if ($ex_name3 != '')
		{
			$extra_sql .= ' OR e.exercise_name = :exercise_name_three';
			$params[] = array(':exercise_name_three', strtolower(trim($ex_name3)), 'str');
		}
		if ($ex_name4 != '')
		{
			$extra_sql .= ' OR e.exercise_name = :exercise_name_four';
			$params[] = array(':exercise_name_four', strtolower(trim($ex_name4)), 'str');
		}
		if ($ex_name5 != '')
		{
			$extra_sql .= ' OR e.exercise_name = :exercise_name_five';
			$params[] = array(':exercise_name_five', strtolower(trim($ex_name5)), 'str');
		}
		$repsql = ($reps != 0) ? ' AND pr.pr_reps = :reps' : '';
		// load all preceeding prs
		$query = "SELECT pr_weight, pr_reps, pr_date, e.exercise_name, pr_1rm FROM exercise_records pr
				LEFT JOIN exercises e ON (e.exercise_id = pr.exercise_id)
				WHERE pr.user_id = :user_id" . $repsql . " AND
				(e.exercise_name = :exercise_name_one OR e.exercise_name = :exercise_name_two $extra_sql)
				ORDER BY pr_date ASC";
		$params[] = array(':exercise_name_one', strtolower(trim($ex_name1)), 'str');
		$params[] = array(':exercise_name_two', strtolower(trim($ex_name2)), 'str');
		$params[] = array(':reps', $reps, 'int');
		$params[] = array(':user_id', $user_id, 'int');
		$db->query($query, $params);
		$prs = array();
		$highest = array(); // for est 1rm so you only show the actual PRs
		while ($row = $db->fetch())
		{
			if (!isset($prs[$row['exercise_name']]))
				$prs[$row['exercise_name']] = array();
			if ($reps == 0)
			{
				if (!isset($highest[$row['exercise_name']]))
					$highest[$row['exercise_name']] = 0;
				if ($highest[$row['exercise_name']] < $row['pr_1rm'])
				{
					$highest[$row['exercise_name']] = $prs[$row['exercise_name']][$row['pr_date']] = $row['pr_1rm'];
				}
			}
			else
			{
				$prs[$row['exercise_name']][$row['pr_date']] = $row['pr_weight'];
			}
		}
		return $prs;
	}

	public function build_pr_graph_data($data, $type = 'rep')
	{
		global $user;

		$graph_data = '';
		$showreps = array_flip(explode('|', $user->user_data['user_showreps']));
		$showreps['Approx. 1'] = 0;
		foreach ($data as $rep => $prs)
		{
			if ($type != 'rep' || isset($showreps[$rep]))
			{
				$graph_data .= "var dataset = [];\n";
				foreach ($prs as $date => $weight)
				{
					$date = strtotime($date . ' 00:00:00') * 1000;
					$weight = correct_weight($weight, 'kg', $user->user_data['user_unit']);
					$graph_data .= "\tdataset.push({x: new Date($date), y: $weight, shape:'circle'});\n";
				}
				$type_string = ($type == 'rep') ? ' rep max' : '';
				$graph_data .= "prHistoryChartData.push({\n\tvalues: dataset,\n\tkey: '{$rep}{$type_string}'\n});\n";
			}
		}
		return $graph_data;
	}

	// TODO can I merge all the graph data function into one? or make a new class for them?
	public function build_bodyweight_graph_data($data)
	{
		global $user;
		$graph_data = '';
		$graph_data .= "var dataset = [];\n";
		foreach ($data as $date => $weight)
		{
			$date = strtotime($date . ' 00:00:00') * 1000;
			$weight = correct_weight($weight, 'kg', $user->user_data['user_unit']);
			$graph_data .= "\tdataset.push({x: new Date($date), y: $weight, shape:'circle'});\n";
		}
		$graph_data .= "prHistoryChartData.push({\n\tvalues: dataset,\n\tkey: 'Bodyweight'\n});\n";
		return $graph_data;
	}

	public function list_exercises($user_id, $count = true)
	{
		global $db;
		// load all exercises
		if ($count)
		{
			$query = "SELECT e.exercise_name, COUNT(logex_id) as COUNT FROM exercises e
					LEFT JOIN log_exercises l ON (l.exercise_id = e.exercise_id)
					WHERE e.user_id = :user_id GROUP BY l.exercise_id
					ORDER BY COUNT DESC";
		}
		else
		{
			$query = "SELECT exercise_id, exercise_name FROM exercises
					WHERE user_id = :user_id
					ORDER BY exercise_name ASC";
		}
		$params = array(
			array(':user_id', $user_id, 'int')
		);
		$db->query($query, $params);
		return $db->fetchall();
	}

	public function list_exercise_logs($user_id, $from_date, $to_date, $exercise_name = '')
	{
		global $db, $user;

		$params = array();
		$extra_sql = '';
		// use data limits
		if (!empty($from_date))
		{
			$extra_sql .= " AND logitem_date >= :from_date";
			$params[] = array(':from_date', $from_date, 'str');
		}
		if (!empty($to_date))
		{
			$extra_sql .= " AND logitem_date <= :to_date";
			$params[] = array(':to_date', $to_date, 'str');
		}

		// load all exercises
		if (!empty($exercise_name))
		{
			$query = "SELECT i.*, lx.logex_volume, lx.logex_reps, lx.logex_sets, lx.logex_comment, lx.logex_1rm FROM log_items As i
					LEFT JOIN exercises ex ON (ex.exercise_id = i.exercise_id)
					LEFT JOIN log_exercises As lx ON (lx.exercise_id = ex.exercise_id AND lx.log_id = i.log_id)
					WHERE ex.user_id = :user_id AND ex.exercise_name = :exercise_name
					$extra_sql
					ORDER BY logitem_date DESC, logitem_id ASC";
			$params[] = array(':user_id', $user_id, 'int');
			$params[] = array(':exercise_name', $exercise_name, 'str');
		}
		else
		{
			$query = "SELECT i.*, lx.logex_volume, lx.logex_reps, lx.logex_sets, lx.logex_comment, lx.logex_1rm FROM log_items As i
					LEFT JOIN log_exercises As lx ON (lx.log_id = i.log_id)
					WHERE i.user_id = :user_id
					$extra_sql
					ORDER BY logitem_date DESC, logitem_id ASC";
			$params[] = array(':user_id', $user_id, 'int');
		}
		$db->query($query, $params);

		// organise the data
		$max_reps = $max_sets = $max_vol = $max_rm = 0;
		$data = array();
		while ($row = $db->fetch())
		{
			// set log globals
			if (!isset($data[$row['log_id']]))
			{
				$row['logex_volume'] = correct_weight($row['logex_volume'], 'kg', $user->user_data['user_unit']);
				$row['logex_1rm'] = correct_weight($row['logex_1rm'], 'kg', $user->user_data['user_unit']);
				$data[$row['log_id']] = array(
					'logitem_date' => $row['logitem_date'],
					'logex_volume' => $row['logex_volume'],
					'logex_1rm' => $row['logex_1rm'],
					'logex_reps' => $row['logex_reps'],
					'logex_sets' => $row['logex_sets'],
					'logex_comment' => $row['logex_comment'],
					'sets' => array() // ready for the rest of the data
					);
				// set max values
				if ($max_sets < $row['logex_sets'])
					$max_sets = $row['logex_sets'];
				if ($max_reps < $row['logex_reps'])
					$max_reps = $row['logex_reps'];
				if ($max_rm < $row['logex_1rm'])
					$max_rm = $row['logex_1rm'];
			}
			$weight = correct_weight($row['logitem_weight'], 'kg', $user->user_data['user_unit']);
			// include your failed reps in the total volume
			if ($user->user_data['user_volumeincfails'] == 1 && $row['logitem_reps'] == 0)
			{
				// we are assuming the failed rep now counts as 1 as you still attempted it
				$data[$row['log_id']]['logex_volume'] += $weight * $row['logitem_sets'];
			}
			// check the vol again + new set max
			if ($max_vol < $data[$row['log_id']]['logex_volume'])
				$max_vol = $data[$row['log_id']]['logex_volume'];
			$data[$row['log_id']]['sets'][] = array(
				'logitem_weight' => $weight,
				'is_bw' => $row['is_bw'],
				'logitem_reps' => $row['logitem_reps'],
				'logitem_sets' => $row['logitem_sets'],
				'logitem_rpes' => $row['logitem_rpes'],
				'logitem_comment' => $row['logitem_comment'],
				'is_pr' => $row['is_pr'],
				'est1rm' => correct_weight($row['logitem_1rm'], 'kg', $user->user_data['user_unit']),
				);
		}
		$data[0] = array(
			'max_reps' => $max_reps,
			'max_sets' => $max_sets,
			'max_vol' => $max_vol,
			'max_rm' => $max_rm
		);
		return $data;
	}

	public function get_average_intensity($volume, $reps, $sets_data, $current_1rm)
	{
		global $user;
		// average intensity is limited
		if ($user->user_data['user_limitintensity'] > 0)
		{
			foreach ($sets_data as $set)
			{
				if (isset($set['logitem_weight']) && is_numeric($set['logitem_weight']) && $set['logitem_weight'] < $current_1rm * ($user->user_data['user_limitintensity']/100))
				{
					// remove from volume
					$volume = $volume - ($set['logitem_weight'] * $set['logitem_reps'] * $set['logitem_sets']);
					$reps = $reps - ($set['logitem_reps'] * $set['logitem_sets']);
				}
			}
		}

		if ($user->user_data['user_viewintensityabs'] == 0)
		{
			$average_intensity = (($volume / $reps) / $current_1rm) * 100;
		}
		else
		{
			$average_intensity = ($volume / $reps);
		}
		return round($average_intensity, 1);
	}
}
?>
