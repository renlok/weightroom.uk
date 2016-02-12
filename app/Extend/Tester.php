<?php

/*
rewritten:
- special_tags
*/
class Tester
{
	public function saveLogData ()
	{
		// save log
		$this->log->save();
		$log_id = $this->log->log_id;
		unset($this->log);

		// insert the log_exercises
		for ($i = 0, $count = count($this->log_exercises); $i < $count; $i++)
		{
			// save exercise
			$this->log_exercises[$i]->log_id = $log_id;
			$this->log_exercises[$i]->save();
			array_walk($this->log_items[$i], array($this, 'updateLogId'), $log_id);
			$this->log_exercises[$i]->log_items()->saveMany($this->log_items[$i]);
			unset($this->log_exercises[$i]);
			unset($this->log_items[$i]);
		}
	}

	private function updateLogId (&$log_item, $key, $log_id)
	{
		$log_item[$key]->log_id = $log_id;
	}

	public function formatLogData ($is_new)
	{
		if (!$is_new)
		{
			// load old log
			$this->log = Log::where('log_date', $this->log_date)
							->where('user_id', $this->user->user_id)
							->firstOrFail();
		}
		else
		{
			// build a new log
			$this->log = new Log;
			$this->log->log_date = $this->log_date;
			$this->log->user_id = $this->user->user_id;
		}
		// set log values
		$this->log->log_text = $this->log_text;
		$this->log->log_comment = trim($this->log_data['comment']);
		$this->log->log_weight = $this->user_weight;

		// TODO: update users weight Parser:316-326

		// TODO: this doesn't need to be in a function
		$this->new_prs = [];
		$this->log_exercises = []; // an array of model objects
		$this->exercises = []; // useful information about log exercses
		$this->log_items = [];
		// add all of the exercise details
		for ($i = 0, $count_i = count($this->log_data['exercises']); $i < $count_i; $i++)
		{
			$item = $this->log_data['exercises'][$i];
			// create new Log_exercise
			$this->log_exercises[$i] = new Log_exercise;
			$this->log_items[$i] = [];
			// set exercise name
			$exercise_name = trim($item['name']);

            // get exercise information
            $exercise = Exercise::getexercise($exercise_name, $this->user->user_id)->first();
            if ($exercise == null)
            {
				// new exercise
                $exercise = Exercise::create([
                    'exercise_name' => $exercise_name,
                    'user_id' => $this->user->user_id
                ]);
				$this->exercises[$i]['new'] = true;
				$this->exercises[$i]['time'] = false;
				$this->exercises[$i]['endurance'] = false;
            }
            else
            {
	            $this->exercises[$i]['new'] = false;
                $this->exercises[$i]['time'] = $exercise->is_time;
                $this->exercises[$i]['endurance'] = $exercise->is_endurance;
            }
			$this->exercises[$i]['id'] = $exercise->exercise_id;
			// insert log_exercise data
			$this->log_exercises[$i]->log_date = $this->log_date;
			$this->log_exercises[$i]->user_id = $this->user->user_id;
			$this->log_exercises[$i]->exercise_id = $this->exercises[$i]['id'];
			$this->log_exercises[$i]->logex_comment = trim($item['comment']);
			$this->log_exercises[$i]->logex_order = $i;

            $prs = [];
			// TODO: merge these into a single query
            $prs['E'] = Exercise_record::getexerciseprs($this->user->user_id, $this->log_date, $exercise_name, true, true)->toArray();
            $prs['T'] = Exercise_record::getexerciseprs($this->user->user_id, $this->log_date, $exercise_name, true, false)->toArray();
            $prs['W'] = Exercise_record::getexerciseprs($this->user->user_id, $this->log_date, $exercise_name, false, false)->toArray();

			$max_estimate_rm = Exercise_record::getlastest1rm($this->user->user_id, $exercise_name);
			for ($j = 0, $count_j = count($item['data']); $j < $count_j; $j++)
			{
				$set = $item['data'][$j];
				$this->log_items[$i][$j] = new Log_item;
				// guess what these should be from exercise data
                $this->log_items[$i][$j]->is_time = $this->exercises[$i]['time'];
                $this->log_items[$i][$j]->is_endurance = $this->exercises[$i]['endurance'];
				// clean up set data
				$set = $this->cleanSetData ($set, $i, $j);
                // check comment for special tags
				$this->checkSpecialTags ($set['C'], $i, $j);
				$this->setAbsoluteWeight ($set, $i, $j);
				// calculate volume data
				$this->updateVolumes ($set, $i, $j);
				// check its a pr
                if ($this->checkPR ($prs, $set, $i, $j))
				{
					$this->log_items[$i][$j]->is_pr = true;
                    $pr_type = ($this->log_items[$i][$j]->is_time == true) ?
								(($this->log_items[$i][$j]->is_endurance == true) ? 'E' : 'T') : 'W';
                    // the user has set a pr we need to add/update it in the database
					//TODO: rewrite this
                    $this->update_prs ($this->exercises[$i]['id'], $this->log_items[$i][$j]->logitem_abs_weight, $set['R'], $is_time, $is_endurance);
                    if (!isset($new_prs[$exercise_name]))
					{
                        $new_prs[$exercise_name] = array('W' => [], 'T' => [], 'E' => []);
					}
                    $new_prs[$exercise_name][$pr_type][$set['R']][] = $this->log_items[$i][$j]->logitem_abs_weight;
                    $prs[$pr_type][$set['R']] = $this->log_items[$i][$j]->logitem_abs_weight;
				}
                if ($this->exercises[$i]['new'])
                {
                    $this->exercises[$i]['time'] = $this->log_items[$i][$j]->is_time;
                    $this->exercises[$i]['endurance'] = $this->log_items[$i][$j]->is_endurance;
                }
				$this->log_items[$i][$j]->logex_1rm = $this->generate_rm ($this->log_items[$i][$j]->logitem_abs_weight, $set['R']);
				// get estimate 1rm
				if ($max_estimate_rm < $this->log_items[$i][$j]->logex_1rm)
				{
					$max_estimate_rm = $this->log_items[$i][$j]->logex_1rm;
				}
				// fill in remaining values
				$this->log_items[$i][$j]->log_date = $this->log_date;
				$this->log_items[$i][$j]->user_id = $this->user->user_id;
				$this->log_items[$i][$j]->exercise_id = $this->exercises[$i]['id'];
				$this->log_items[$i][$j]->logitem_weight = $set['W'];
				$this->log_items[$i][$j]->logitem_time = $set['T'];
				$this->log_items[$i][$j]->logitem_reps = $set['R'];
				$this->log_items[$i][$j]->logitem_sets = $set['S'];
				$this->log_items[$i][$j]->logitem_order = $j;
				$this->log_items[$i][$j]->logex_order = $i;
				$this->log_items[$i][$j]->logitem_pre = (!isset($set['P']) || $set['P'] == NULL) ? NULL : $set['P'];
			}
			$this->log_exercises[$i]->logex_1rm = $max_estimate_rm;
		}

		//return your new records :)
        if (count($new_prs) > 0)
        {
            Session::flash('new_prs', $new_prs);
        }
	}

	public function cleanSetData ($set, $i, $j)
	{
		if (isset($set['W']))
		{
			$set['W'][0] = str_replace(' ', '', $set['W'][0]);
			if (strtoupper(substr($set['W'][0], 0, 2)) == 'BW')
			{
				$this->log_items[$i][$j]->is_bw = true;
				$set['W'][0] = substr($set['W'][0], 2);
			}
			$set['W'] = $this->correct_units_for_database ($set['W'], 'W');
			$set['T'] = NULL;
		}
		elseif (isset($set['T']))
		{
			$set['T'][0] = str_replace(' ', '', $set['T'][0]);
			$set['T'] = $this->correct_units_for_database ($set['T'], 'T');
			$this->log_items[$i][$j]->is_time = true;
			$set['W'] = NULL;
		}
		$set['R'] = (isset($set['R'])) ? intval($set['R']) : 1;
		$set['S'] = (isset($set['S'])) ? intval($set['S']) : 1;
		$set['C'] = (isset($set['C'])) ? $set['C'] : '';
		return $set;
	}

	private function checkSpecialTags ($string, $i, $j)
	{
        if (empty($string))
        {
            return 0;
        }
		$string = trim(strtolower($string));
		// line is warmup
		if ($this->isWarmupTag ($string))
		{
			$this->log_items[$i][$j]->is_warmup = true;
		}
        elseif ($this->isEnduranceTag ($string))
		{
			$this->log_items[$i][$j]->is_endurance = true;
			$this->log_items[$i][$j]->is_time = true;
		}
		else
		{
	        $parts = explode('|', $string);
	        if (count($parts) > 1)
	        {
	            $return = [];
	            foreach ($parts as $part)
	            {
	                if ($this->isWarmupTag ($part))
	        		{
	        			$this->log_items[$i][$j]->is_warmup = true;
	        		}
	                elseif ($this->isEnduranceTag ($string))
	        		{
	                    $this->log_items[$i][$j]->is_endurance = true;
						$this->log_items[$i][$j]->is_time = true;
	        		}
	                else
	                {
	                    $this->log_items[$i][$j]->logitem_comment .= $part;
	                }
	            }
	        }
			else
			{
				$this->log_items[$i][$j]->logitem_comment = $string;
			}
		}
	}

	private function isWarmupTag ($part)
	{
		if ($part == 'w' || $part == 'warmup' || $part == 'warm-up' || $part == 'warm up' || $part == 'wu')
		{
			return true;
		}
		return false;
	}

	private function isEnduranceTag ($part)
	{
		if ($part == 'e' || $part == 'endurance')
		{
			return true;
		}
		return false;
	}

	private function setAbsoluteWeight ($set, $i, $j)
	{
		if ($this->log_items[$i][$j]->is_bw)
		{
			$this->log_items[$i][$j]->logitem_abs_weight = floatval($set['W'] + $this->user_weight);
		}
		elseif ($this->log_items[$i][$j]->is_time)
		{
			$this->log_items[$i][$j]->logitem_abs_weight = floatval($set['T']);
		}
		else
		{
			$this->log_items[$i][$j]->logitem_abs_weight = floatval($set['W']);
		}
	}

	private function updateVolumes ($set, $i, $j)
	{
		if (!$this->log_items[$i][$j]->is_time)
		{
			return false;
		}

		$item_volume = $this->log_items[$i][$j]->logitem_abs_weight * $set['R'] * $set['S'];
		$this->log_exercises[$i]->logex_volume += $item_volume;
		$this->log_exercises[$i]->logex_reps += ($set['R'] * $set['S']);
		$this->log_exercises[$i]->logex_sets += $set['S'];
		$this->log->log_total_volume += $item_volume;
		$this->log->log_total_reps += ($set['R'] * $set['S']);
		$this->log->log_total_sets += $set['S'];

		if ($set['R'] == 0)
		{
			$this->log_exercises[$i]->logex_failed_volume += ($this->log_items[$i][$j]->logitem_abs_weight * $set['S']);
			$this->log_exercises[$i]->logex_failed_sets += $set['S'];
			$this->log->log_failed_volume += ($this->log_items[$i][$j]->logitem_abs_weight * $set['S']);
			$this->log->log_failed_sets += $set['S'];
		}

		if ($this->log_items[$i][$j]->is_warmup)
		{
			$this->log_exercises[$i]->logex_warmup_volume += $item_volume;
			$this->log_exercises[$i]->logex_warmup_reps += ($set['R'] * $set['S']);
			$this->log_exercises[$i]->logex_warmup_sets += $set['S'];
			$this->log->log_warmup_volume += $item_volume;
			$this->log->log_warmup_reps += ($set['R'] * $set['S']);
			$this->log->log_warmup_sets += $set['S'];
		}
	}

	private function checkPR (&$prs, $set, $i, $j)
	{
		if ($set['R'] == 0)
		{
			return false;
		}
		if ($this->log_items[$i][$j]->is_endurance)
		{
			// endurance
			if (isset($prs['E'][$set['R']]) &&
				floatval($prs['E'][$set['R']]) < $this->log_items[$i][$j]->logitem_abs_weight)
			{
				return true;
			}
		}
		elseif ($this->log_items[$i][$j]->is_time)
		{
			// time
			if (isset($prs['T'][$set['R']]) &&
				floatval($prs['T'][$set['R']]) > $this->log_items[$i][$j]->logitem_abs_weight)
			{
				return true;
			}
		}
		else
		{
			// weight
			if (isset($prs['W'][$set['R']]) &&
				floatval($prs['W'][$set['R']]) < $this->log_items[$i][$j]->logitem_abs_weight)
			{
				return true;
			}
		}
		return false;
	}

	private function setExerciseDefaultTypes ()
	{
		// if new exercise set if it a time based exercise
		if ($this->exercises[$i]['new'])
		{
			$update_exercises = [];
			if ($this->exercises[$i]['time'])
			{
				$update_exercises['is_time'] = 1;
			}
			if ($this->exercises[$i]['endurance'])
			{
				$update_exercises['is_endurance'] = 1;
			}
			if (count($update_exercises) > 0)
			{
				DB::table('exercises')->where('exercise_id', $this->exercises[$i]['id'])->update($update_exercises);
			}
		}
	}
}
