<?php
class parser
{
  // the predefined data variables
  var $units; // array of units for each block type
  var $format_types_all; // all possible formats for all possible blocks
  var $next_values_all; // flags that show a certain type of block is coming up
  var $format_follows; // what should come after each block type
  // the working variables
  var $accepted_char;
  var $accepted_chars;
  var $format_type;
  var $current_blocks; // the blocks that are expected
  var $next_values;
  // data dumps
  var $number_dump; // no current use
  var $format_dump;
  var $chunk_dump;
  // final array
  var $log_data;
	// information for the database
	var $bodyweight;

  public function parser ($log_text, $bodyweight)
  {
    // build the initial startup data
    $this->construct_globals ();
		$this->bodyweight = $bodyweight;
    $exercise = '';
		$position = 0; // a pointer for when exercise was done
    $this->log_data = array('comment' => '', 'exercises' => array()); // the output array
    // convert log_text to array
    $log_lines = explode("\n", $log_text);
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
				$position++;
				$exercise = substr($line, 1); // set exercise marker
				// add new exercise group to array
				$this->log_data['exercises'][$position] = array(
						'name' => $exercise,
						'comment' => '',
						'data' => array());
				continue; // end this loop
			}

			// no exercise yet
			if ($exercise == '')
			{
				if (!empty($this->log_data['comment']))
				{
					$this->log_data['comment'] .= '<br>';
				}
				$this->log_data['comment'] .= $line;
				continue; // end this loop
			}
			elseif (is_numeric($line[0]) || $line[0] == 'B')
			{
				$this->log_data['exercises'][$position]['data'] = array_merge($this->log_data['exercises'][$position]['data'], $this->parse_line ($line));
			}
			else
			{
				$this->log_data['exercises'][$position]['comment'] .= $line;
			}
    }
  }

  private function parse_line ($line)
  {
    // build the initial startup data
    $this->current_blocks = array('W', 'T', 'C');
    $this->build_next_formats ();
    $this->build_accepted_char ();
    $this->build_accepted_chars ();
    $this->number_dump = '';
    $this->format_dump = '';
    $this->chunk_dump = '';
		// for when a line contains commas we can add them as multiple sets
    $multiline = 0;
		$multiline_max = 0;
    $output_data = array();
    $string_array = str_split($line);
    foreach($string_array as $chr)
    {
      // if the character is a space just add it to the chunk and continue
      if ($chr == ' ')
      {
        $this->chunk_dump .= ' ';
        continue;
      }
      $format_chr = $this->format_character($chr);
      // check character is in format and empty accepted_chars counts as allowing anything
      if (count($this->accepted_chars) == 0 || in_array($format_chr, $this->accepted_chars))
      {
        $this->build_accepted_char ();
        $this->build_accepted_chars ($format_chr);
        if (is_numeric($chr))
        {
          $this->number_dump .= $chr;
          // check the last value of the format_dump if its not 0 already set it to 0
          if (!isset($this->format_dump[strlen($this->format_dump) - 1]) || $this->format_dump[strlen($this->format_dump) - 1] != '0')
          {
            $this->format_dump .= '0';
          }
        }
        else
        {
          // the character is not a number so just add it to the format
          $this->format_dump .= $chr;
        }
        $this->chunk_dump .= $chr;
      }
      else
      {
        // check the previous chunk is valid
        if ($this->format_check($this->format_dump))
        {
					// the current chunk has finshed do something
					$output_data[$multiline][$this->current_blocks[0]] = $this->clean_units(trim($this->chunk_dump), $this->current_blocks[0]);
					// reset the dumps
					$this->number_dump = '';
					$this->format_dump = '';
					$this->chunk_dump = '';
    			// we are repeating the chunk
					// TODO: count($this->current_blocks) == 1 && doesn't work with simple inputs can I get around this?
    			if ($this->current_blocks[0] != 'C' && $format_chr == ',')
    			{
    				$multiline++;
						// new multiline_max?
						if ($multiline_max < $multiline)
						{
							$multiline_max = $multiline;
						}
    			}
    			else
    			{
    				// find the options for the next format
    				if (in_array($format_chr, $this->next_values))
    				{
    					// find what block comes next
    					$this->current_blocks = array_keys ($this->next_values, $chr);
    					// reset $format_type + next values
    					$this->build_next_formats ();
    					// rebuild everything
    					$this->build_accepted_char ();
    					$this->build_accepted_chars ();
    				}
    				else
    				{
    					// assume it is a comment
    					$this->accepted_chars = array();
    					$this->accepted_char = array();
              $this->chunk_dump .= $chr;
    				}
    				$multiline = 0;
    			}
        }
        else
        {
          $this->flag_error('Format Error');
          break;
        }
      }
    }
		// add the last chunk to the data array
		if ($this->current_blocks[0] != 'C')
		{
			$this->chunk_dump = $this->clean_units($this->chunk_dump, $this->current_blocks[0]);
		}
		if (isset($output_data[$multiline][$this->current_blocks[0]]))
		{
			$output_data[$multiline][$this->current_blocks[0]] .= $this->chunk_dump;
		}
		else
		{
			$output_data[$multiline][$this->current_blocks[0]] = $this->chunk_dump;
		}
		// do something with $multiline_max
		if ($multiline_max > 0)
		{
			// temporarly remove the comment
			if (isset($output_data[0]['C']))
			{
				$comment = $output_data[0]['C'];
				unset($output_data[0]['C']);
			}
			// the first line should always be complete so find what was given
			$blocks = array_keys($output_data[0]);
			$last_values = array();
			for ($i = 0; $i <= $multiline_max; $i++)
			{
				// check each block
				foreach ($blocks as $block)
				{
					if (!isset($output_data[$i][$block]))
					{
						$output_data[$i][$block] = $last_values[$block];
					}
				}
				$last_values = $output_data[$i];
			}
			// re add the comment to the end 
			$output_data[$multiline_max]['C'] = $comment;
		}

    return $output_data;
  }
	
	public function store_log_data ($log_date)
	{
		global $db, $user, $log;
		// clear old entries
		$query = "DELETE FROM log_exercises WHERE logex_date = :log_date AND user_id = :user_id";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user->user_id, 'int')
		);
		$db->query($query, $params);
		$query = "DELETE FROM log_items WHERE logitem_date = :log_date AND user_id = :user_id";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user->user_id, 'int')
		);
		$db->query($query, $params);
		// clear out old pr data
		$query = "DELETE FROM exercise_records WHERE pr_date = :log_date AND user_id = :user_id";
		$params = array(
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user->user_id, 'int')
		);
		$db->query($query, $params);

		// delete log and exit function if no data
		if (count($this->log_data) <= 1)
		{
			$query = "DELETE FROM logs WHERE log_date = :log_date AND user_id = :user_id";
			$params = array(
				array(':log_date', $log_date, 'str'),
				array(':user_id', $user->user_id, 'int')
			);
			$db->query($query, $params);
			return false;
		}

		//check if its new
		if ($log->is_valid_log($user->user_id, $log_date))
		{
			// update log entry
			$query = "UPDATE logs SET log_text = :log_text, log_comment = :log_comment, log_weight = :log_weight WHERE log_date = :log_date AND user_id = :user_id";
			$params = array(
				array(':log_text', $log_text, 'str'),
				array(':log_comment', $this->replace_video_urls($this->log_data['comment']), 'str'),
				array(':log_weight', $user_weight, 'float'),
				array(':log_date', $log_date, 'str'),
				array(':user_id', $user->user_id, 'int')
			);
			$db->query($query, $params);
		}
		else
		{
			// add a new entry
			$query = "INSERT INTO logs (log_text, log_comment, log_weight, log_date, user_id) VALUES (:log_text, :log_comment, :log_weight, :log_date, :user_id)";
			$params = array(
				array(':log_text', $log_text, 'str'),
				array(':log_comment', $this->replace_video_urls($this->log_data['comment']), 'str'),
				array(':log_weight', $user_weight, 'float'),
				array(':log_date', $log_date, 'str'),
				array(':user_id', $user->user_id, 'int')
			);
			$db->query($query, $params);
		}
		// todays log then update weight
		if ($log_date == date("Y-m-d"))
		{
			$query = "UPDATE users SET user_weight = :log_weight WHERE user_id = :user_id";
			$params = array(
				array(':log_weight', $user_weight, 'float'),
				array(':user_id', $user->user_id, 'int')
			);
			$db->query($query, $params);
		}
		else
		{
			// update future logs
			$this->update_user_weights ($user->user_id, $log_date, $user_weight);
		}

		$log_id = $log->load_log ($user->user_id, $log_date, 'log_id');
		$log_id = $log_id['log_id'];
		$new_prs = array();
		// add all of the exercise details
		foreach ($this->log_data['exercises'] as $item)
		{
			// set exercise name
			$exercise = $item['name'];
			// reset totals
			$total_volume = $total_reps = $total_sets = 0;
			$exercise_id = $log->get_exercise_id ($user->user_id, $exercise);
			$prs = $log->get_prs ($user->user_id, $log_date, $exercise);
			$max_estimate_rm = 0;
			foreach ($item['data'] as $set)
			{
				// TODO: fill in missing groups
				$is_bw = false;
				if (isset($set['W']))
				{
					$set['W'][0] = str_replace(' ', '', $set['W'][0]);
					if (substr($set['W'][0], 0, 2) == 'BW')
					{
						$is_bw = true;
						$set['W'][0] = substr($set['W'][0], 2);
					}
					$set['W'] = correct_units_for_database ($set['W'], 'W');
					$is_time = false;
					$set['T'] = null;
				}
				elseif (isset($set['T']))
				{
					$set['T'][0] = str_replace(' ', '', $set['T'][0]);
					$set['T'] = correct_units_for_database ($set['T'], 'T');
					$is_time = true;
					$set['W'] = null;
				}
				$absolute_weight = ($is_bw == false) ? $set['W'] : ($set['W'] + $user_weight);
				$total_volume += ($absolute_weight * $set['R'] * $set['S']);
				$total_reps += ($set['R'] * $set['S']);
				$total_sets += $set['S'];
				$is_pr = false;
				// check its a pr
				if ((!isset($prs[$set['R']]) || floatval($prs[$set['R']]) < floatval($absolute_weight)) && $set['R'] != 0)
				{
					$is_pr = true;
					// the user has set a pr we need to add/update it in the database
					$this->update_prs ($user->user_id, $log_date, $exercise_id, $absolute_weight, $set['R']);
					if (!isset($new_prs[$exercise]))
						$new_prs[$exercise] = array();
					$new_prs[$exercise][$set['R']][] = $absolute_weight;
					// update pr array
					$prs[$set['R']] = $absolute_weight;
				}
				$estimate_rm = $log->generate_rm ($absolute_weight, $set['R']);
				// get estimate 1rm
				if ($max_estimate_rm < $estimate_rm)
				{
					$max_estimate_rm = $estimate_rm;
				}
				// insert into log_items
				// TODO: add time, is_time to db
				$query = "INSERT INTO log_items (logitem_date, log_id, user_id, exercise_id, logitem_weight, logitem_abs_weight, logitem_reps, logitem_sets, logitem_rpes, logitem_comment, logitem_1rm, is_pr, is_bw, logitem_order)
							VALUES (:logitem_date, :log_id, :user_id, :exercise_id, :logitem_weight, :logitem_abs_weight, :logitem_reps, :logitem_sets, :logitem_rpes, :logitem_comment, :logitem_rm, :is_pr, :is_bw, :logitem_order)";
				$params = array(
					array(':logitem_date', $log_date, 'str'),
					array(':log_id', $log_id, 'int'),
					array(':user_id', $user->user_id, 'int'),
					array(':exercise_id', $exercise_id, 'int'),
					array(':logitem_weight', $set['W'], 'float'),
					array(':logitem_abs_weight', $absolute_weight, 'float'),
					array(':logitem_reps', $set['R'], 'int'),
					array(':logitem_sets', $set['S'], 'int'),
					array(':logitem_comment', $set['line'], 'str'),
					array(':logitem_rm', $estimate_rm, 'float'),
					array(':is_pr', (($is_pr == false) ? 0 : 1), 'int'),
					array(':is_bw', (($is_bw == false) ? 0 : 1), 'int'),
					array(':logitem_order', $set['position'], 'int'),
				);
				if (!isset($set['P']) || $set['P'] == NULL)
					$params[] = array(':logitem_rpes', NULL, 'int');
				else
					$params[] = array(':logitem_rpes', $rpe_arr[$i], 'float');
				$db->query($query, $params);
			}
			// insert into log_exercises
			$query = "INSERT INTO log_exercises (logex_date, log_id, user_id, exercise_id, logex_volume, logex_reps, logex_sets, logex_1rm, logex_comment, logex_order)
					VALUES (:logex_date, :log_id, :user_id, :exercise_id, :logex_volume, :logex_reps, :logex_sets, :logex_rm, :logex_comment, :logex_order)";
			$params = array(
				array(':logex_date', $log_date, 'str'),
				array(':log_id', $log_id, 'int'),
				array(':user_id', $user->user_id, 'int'),
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

		//return your new records :)
		return $new_prs;
	}
	
	private function correct_units_for_database ($input, $type)
	{
		// TODO: find a cleaner way to do this if possible
		if ($type == 'T')
		{
			// expode : parts
			$time_parts = explode (':', $input[0]);
			if (count($time_parts) == 2)
			{
				$input[0] = $time_parts[1] + ($time_parts[0] * 60);
			}
			elseif (count($time_parts) == 3)
			{
				$input[0] = $time_parts[2] + ($time_parts[1] * 60) + ($time_parts[0] * 60 * 60);
			}
			if ($input[1] == '')
			{
				return $input[0];
			}
			else
			{
				return correct_time($input[0], $input[1], 's');
			}
		}
		elseif ($type == 'W')
		{
			// users default is lb
			if ($user->user_data['user_unit'] == 2 && $input[1] == '')
			{
				return correct_weight($input[0], 'lb', 1);
			}
			elseif ($user->user_data['user_unit'] == 1 && $input[1] == '')
			{
				return $input[0];
			}
			else
			{
				return correct_weight($input[0], $input[1], 1);
			}
		}
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
		$query = "UPDATE log_items SET is_pr = 0 WHERE user_id = :user_id AND logitem_date > :log_date AND exercise_id = :exercise_id AND logitem_reps = :pr_reps AND logitem_abs_weight < :pr_weight";
		$params = array(
			array(':exercise_id', $exercise_id, 'int'),
			array(':log_date', $log_date, 'str'),
			array(':user_id', $user_id, 'int'),
			array(':pr_reps', $set_reps, 'int'),
			array(':pr_weight', $set_weight, 'float')
		);
		$db->query($query, $params);

		// add past prs if needed
		$query = "SELECT log_id, logitem_abs_weight FROM log_items WHERE user_id = :user_id AND logitem_date < :log_date AND exercise_id = :exercise_id AND logitem_reps = :pr_reps AND logitem_abs_weight > :pr_weight AND is_pr = 0";
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
				array(':pr_weight', $row['logitem_abs_weight'], 'float'),
				array(':pr_reps', $set_reps, 'int'),
				array(':pr_rm', $this->generate_rm($row['logitem_abs_weight'], $set_reps), 'float'),
			);
			$db->query($query, $params);
		}
	}

  private function construct_globals ()
  {
    // pre-defined data
    $this->units = array(
      'T' => array(
              's' => 's',
              'secs' => 's',
              'sec' => 's',
              'seconds' => 's',
              'second' => 's',
              'm' => 'm',
              'mins' => 'm',
              'min' => 'm',
              'minutes' => 'm',
              'minute' => 'm',
              'h' => 'h',
              'hrs' => 'h',
              'hr' => 'h',
              'hours' => 'h',
              'hour' => 'h',
            ),
      'W' => array(
              'kg' => 'kg',
              'kgs' => 'kg',
              'lb' => 'lb',
              'lbs' => 'lb',
            ),
    );
    $this->format_types_all = array(
      'T' => array(
              ('0:0:0'),
              ('0:0'),
              ('0.0'),
              ('0'),
            ),
      'W' => array(
              ('0.0'),
              ('0'),
              ('BW'),
              ('BW+0.0'),
              ('BW+0'),
              ('BW-0.0'),
              ('BW-0'),
            ),
      'R' => array(('0')),
      'S' => array(('0')),
      'P' => array(
              ('0.0'),
              ('0'),
            ),
      'C' => array(('')),
    );
    // add units to the formats
    $this->add_units();
    $this->next_values_all = array(
      'R' => 'x',
      'S' => 'x',
      'P' => '@',
      'C' => '',
    );
    $this->format_follows = array(
      'T' => array('R', 'P', 'C'),
      'W' => array('R', 'P', 'C'),
      'R' => array('S', 'P', 'C'),
      'S' => array('P', 'C'),
      'P' => array('C'),
      'C' => array(''),
    );
  }

  private function build_next_formats ()
  {
    $this->format_type = array();
    $this->next_values = array();
    foreach ($this->current_blocks as $key)
    {
      $this->format_type[$key] = $this->format_types_all[$key];
      $this->next_values = array_merge($this->next_values, array_intersect_key($this->next_values_all, array_flip($this->format_follows[$key])));
    }
  }

  private function build_accepted_char ()
  {
    $this->accepted_char = array();
    foreach ($this->format_type as $key => $sub_type)
    {
      foreach ($sub_type as $val)
      {
        if (isset($this->accepted_char[$key]))
        {
          $this->accepted_char[$key] = array_unique(array_merge($this->accepted_char[$key], str_split($val)));
        }
        else
        {
          $this->accepted_char[$key] = array_unique(str_split($val));
        }
      }
    }
  }

  private function build_accepted_chars ($format_chr = '')
  {
    // not an empty string then do some checks
    if ($format_chr != '')
    {
      // check all formats still valid
      $rebuild_accepted_chars = false;
      foreach ($this->accepted_char as $key => $val)
      {
        if(!in_array($format_chr, $val))
        {
          // remove from accepted_char
          unset($this->accepted_char[$key]);
          unset($this->format_type[$key]);
          unset($this->current_blocks[array_search ($key, $this->current_blocks)]);
          if (count($this->current_blocks) == 0)
          {
            $this->current_blocks = array('C');
          }
  				// rebuild keys
          $this->current_blocks = array_values($this->current_blocks);
          $rebuild_accepted_chars = true;
        }
      }
    }
    else
    {
      $rebuild_accepted_chars = true;
    }
    // if some formats have become invalid rebuild the master format array
    if ($rebuild_accepted_chars)
    {
      if (is_array($this->accepted_char) && count($this->accepted_char) > 0)
      {
        $this->accepted_chars = array_unique(call_user_func_array('array_merge', $this->accepted_char));
      }
      else
      {
        $this->accepted_chars = array();
      }
      $this->build_next_formats ();
    }
  }

  private function format_check($format_dump)
  {
    // the block is a comment so skip the check
    if (isset($this->format_type['C']))
    {
      return true;
    }
    // check if the final format_dump matches a vlid format type
    foreach ($this->format_type as $sub_type)
    {
      foreach ($sub_type as $key => $format_string)
      {
        //foreach ($val as $format_string)
        {
          if ($format_string == $format_dump)
          {
            return true;
          }
        }
      }
    }
    return false;
  }

  private function add_units()
  {
    $dump_all_format_types = $this->format_types_all;
    foreach ($this->units as $type => $unit_types)
    {
      foreach ($unit_types as $unit => $val)
      {
        foreach ($dump_all_format_types[$type] as $format)
        {
          $this->format_types_all[$type][] = $format . $unit;
        }
      }
    }
  }
	
	private function clean_units($block, $block_type)
	{
		// clean units
		if (isset($this->units[$block_type]))
		{
			$end = $this->strposa($block, array_keys($this->units[$block_type]));
			if ($end !== false)
			{
				$block = array(trim(substr($block, 0, $end)), $this->units[$block_type][trim(substr($block, $end))]);
			}
			else
			{
				$block = array(trim($block), '');
			}
		}
		return $block;
	}
	
	private function strposa($haystack, $needle, $offset=0)
	{
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $query)
		{
      if(($return = strpos($haystack, $query, $offset)) !== false) return $return; // stop on first true result
    }
    return false;
	}

  // TODO: add all the possible characters that will be accpected
  private function format_character($chr)
  {
    $output_chr = $chr;
    if (is_numeric($chr))
    {
      $output_chr = '0';
    }
    return $output_chr;
  }

  private function flag_error($error)
  {
    echo $error;
  }
}
?>
