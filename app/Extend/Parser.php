<?php

namespace App\Extend;

use Auth;
use DB;
use App\Log;
use App\User;
use App\Exercise;
use App\Exercise_record;
use Carbon\Carbon;

class Parser
{
    // the predefined data variables
    var $units; // array of units for each block type
    var $format_types_all; // all possible formats for all possible blocks
    var $next_values_all; // flags that show a certain type of block is coming up
    var $format_follows; // what should come after each block type
    var $user;
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
    var $log_text;

    public function parse_text ($log_text)
    {
        // build the initial startup data
        $this->construct_globals ();
        $this->log_text = $log_text;
        $exercise = '';
        $position = -1; // a pointer for when exercise was done
        $this->log_data = array('comment' => '', 'exercises' => array()); // the output array
        // get user
        $this->user = Auth::user();
        // convert log_text to array
        $log_lines = explode("\n", $this->log_text);
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
        if (!empty($this->chunk_dump))
        {
            if ($this->current_blocks[0] != 'C')
            {
                $this->chunk_dump = $this->clean_units($this->chunk_dump, $this->current_blocks[0]);
            }
            if (isset($output_data[$multiline][$this->current_blocks[0]]))
            {
                if (is_array($this->chunk_dump))
                {
                    $output_data[$multiline][$this->current_blocks[0]][0] .= $this->chunk_dump[0];
                    $output_data[$multiline][$this->current_blocks[0]][1] .= $this->chunk_dump[1];
                }
                else
                {
                    $output_data[$multiline][$this->current_blocks[0]] .= $this->chunk_dump;
                }
            }
            else
            {
                $output_data[$multiline][$this->current_blocks[0]] = $this->chunk_dump;
            }
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
			$output_data[$multiline_max]['C'] = (isset($comment)) ? $comment : '';
		}

        return $output_data;
    }

	public function store_log_data ($log_date, $user_weight, $is_new)
	{
		// clear old entries
        DB::table('log_items')->where('logitem_date', $log_date)->where('user_id', $this->user->user_id)->delete();
        DB::table('log_exercises')->where('logex_date', $log_date)->where('user_id', $this->user->user_id)->delete();
        DB::table('exercise_records')->where('pr_date', $log_date)->where('user_id', $this->user->user_id)->delete();

		// delete log and exit function if no data
		if (count($this->log_data) <= 1)
		{
            DB::table('logs')->where('log_date', $log_date)->where('user_id', $this->user->user_id)->delete();
			return false;
		}

		//check if its new
		if (!$is_new)
		{
			// update log entry
            $log_id = DB::table('logs')
                    ->where('log_date', $log_date)
                    ->where('user_id', $this->user->user_id)
                    ->value('log_id');
            DB::table('logs')
                ->where('log_date', $log_date)
                ->where('user_id', $this->user->user_id)
                ->update([
                    'log_text' => $this->log_text,
                    'log_comment' => $this->replace_video_urls($this->log_data['comment']),
                    'log_weight' => $user_weight
                ]);
		}
		else
		{
            // just incase lets run a delete
            DB::table('logs')
                ->where('log_date', $log_date)
                ->where('user_id', $this->user->user_id)
                ->delete();
			// add a new entry
            $log_id = DB::table('logs')
                        ->insertGetId([
                            'log_text' => $this->log_text,
                            'log_comment' => $this->replace_video_urls($this->log_data['comment']),
                            'log_weight' => $user_weight,
                            'log_date' => $log_date,
                            'user_id' => $this->user->user_id
                        ]);
		}
        // values that must be updated later
        $log_warmup_volume = $log_warmup_reps = $log_warmup_sets = $log_total_volume = $log_failed_volume = $log_total_reps = $log_total_sets = 0;
		// todays log then update weight
		if ($log_date == date("Y-m-d"))
		{
            DB::table('users')
                ->where('user_id', $this->user->user_id)
                ->update(['user_weight' => $user_weight]);
		}
		else
		{
			// update future logs
			$this->update_user_weights ($this->user->user_id, $log_date, $user_weight);
		}

		$new_prs = array();
		// add all of the exercise details
		for ($i = 0, $count_i = count($this->log_data['exercises']); $i < $count_i; $i++)
		{
			$item = $this->log_data['exercises'][$i];
			// set exercise name
			$exercise_name = trim($item['name']);
			// reset totals
			$total_volume = $total_reps = $total_sets = 0;
            $logex_warmup_volume = $logex_warmup_reps = $logex_warmup_sets = 0;
            // get exercise information
            $exercise = Exercise::getexercise($exercise_name, $this->user->user_id)->first();
            // new exercise
            if ($exercise == null)
            {
                $exercise_is_new = true;
                $exercise_id = Exercise::insertGetId([
                    'exercise_name' => $exercise_name,
                    'user_id' => $this->user->user_id
                ]);
                $exercise_is_time = false; // assume this for now
            }
            else
            {
                $exercise_is_new = false;
                $exercise_id = $exercise->exercise_id;
                $exercise_is_time = $exercise->is_time;
            }
            // insert log_exercise
            $log_exercises_id = DB::table('log_exercises')->insertGetId([
                'logex_date' => $log_date,
                'log_id' => $log_id,
                'user_id' => $this->user->user_id,
                'exercise_id' => $exercise_id,
                'logex_comment' => $this->replace_video_urls($item['comment']),
                'logex_order' => $i
            ]);
            $prs = Exercise_record::getexerciseprs($this->user->user_id, $log_date, $exercise_name)
                    ->get()
                    ->groupBy(function ($item, $key) {
                        return ($item['is_time']) ? 'T' : 'W';
                    })
                    ->toArray();
			$max_estimate_rm = 0;
			for ($j = 0, $count_j = count($item['data']); $j < $count_j; $j++)
			{
				$set = $item['data'][$j];
				$is_time = $is_bw = false;
				if (isset($set['W']))
				{
					$set['W'][0] = str_replace(' ', '', $set['W'][0]);
					if (strtoupper(substr($set['W'][0], 0, 2)) == 'BW')
					{
						$is_bw = true;
						$set['W'][0] = substr($set['W'][0], 2);
					}
					$set['W'] = $this->correct_units_for_database ($set['W'], 'W');
					$is_time = false;
					$set['T'] = 0;
				}
				elseif (isset($set['T']))
				{
					$set['T'][0] = str_replace(' ', '', $set['T'][0]);
					$set['T'] = $this->correct_units_for_database ($set['T'], 'T');
					$is_time = true;
					$set['W'] = 0;
				}
				$set['R'] = (isset($set['R'])) ? $set['R'] : 1;
				$set['S'] = (isset($set['S'])) ? $set['S'] : 1;
				$set['C'] = (isset($set['C'])) ? $set['C'] : '';
                $is_warmup = false;
                // check if the comment is a special tag
                if($options = $this->special_tags($set['C']) != 0)
                {
                    $set['C'] = '';
                    if ($options == 'w')
                    {
                        $is_warmup = true;
                    }
                }
				$absolute_weight = ($is_bw == false) ? $set['W'] : ($set['W'] + $user_weight);
                $item_volume = ($absolute_weight * $set['R'] * $set['S']);
				$total_volume += $item_volume;
                $log_failed_volume += ($set['R'] == 0) ? ($absolute_weight * $set['S']) : 0;
                // deal the time PRs
				$absolute_weight = floatval(($is_time == true) ? $set['T'] : $absolute_weight);
				$total_reps += ($set['R'] * $set['S']);
				$total_sets += $set['S'];
                if ($is_warmup)
                {
                    $log_warmup_volume += $item_volume;
                    $log_warmup_reps += ($set['R'] * $set['S']);
                    $log_warmup_sets += $set['S'];
                    $logex_warmup_volume += $item_volume;
                    $logex_warmup_reps += ($set['R'] * $set['S']);
                    $logex_warmup_sets += $set['S'];
                }
				$is_pr = false;
				// check its a pr
                if ((($is_time && (!isset($prs['T'][$set['R']]) || floatval($prs['T'][$set['R']]) > floatval($absolute_weight))) || // set time PR
                    (!$is_time && (!isset($prs['W'][$set['R']]) || floatval($prs['W'][$set['R']]) < floatval($absolute_weight)))) && // set weight PR
                    $set['R'] != 0)
				{
					$is_pr = true;
                    $pr_type = ($is_time == true) ? 'T' : 'W';
                    // the user has set a pr we need to add/update it in the database
                    $this->update_prs ($this->user->user_id, $log_date, $exercise_id, $absolute_weight, $set['R'], $is_time);
                    if (!isset($new_prs[$exercise_name]))
                        $new_prs[$exercise_name] = array('W' => array(), 'T' => array());
                    $new_prs[$exercise_name][$pr_type][$set['R']][] = $absolute_weight;
                    // update pr array
                    $prs[$pr_type][$set['R']] = $absolute_weight;
				}
                if ($exercise_is_new)
                {
                    $exercise_is_time = $is_time;
                }
				$estimate_rm = $this->generate_rm ($absolute_weight, $set['R']);
				// get estimate 1rm
				if ($max_estimate_rm < $estimate_rm)
				{
					$max_estimate_rm = $estimate_rm;
				}
				// insert into log_items
                $log_item_data = [
                    'logitem_date' => $log_date,
                    'log_id' => $log_id,
                    'logex_id' => $log_exercises_id,
                    'user_id' => $this->user->user_id,
                    'exercise_id' => $exercise_id,
                    'logitem_weight' => $set['W'],
                    'logitem_abs_weight' => $absolute_weight,
                    'logitem_time' => $set['T'],
                    'logitem_reps' => $set['R'],
                    'logitem_sets' => $set['S'],
                    'logitem_comment' => $set['C'],
                    'logitem_1rm' => $estimate_rm,
                    'is_pr' => $is_pr,
                    'is_bw' => $is_bw,
                    'is_time' => $is_time,
                    'is_warmup' => $is_warmup,
                    'options' => $options,
                    'logitem_order' => $j,
                    'logex_order' => $i,
                ];
				if (!isset($set['P']) || $set['P'] == NULL)
					$log_item_data['logitem_pre'] = NULL;
				else
					$log_item_data['logitem_pre'] = $set['P'];
                DB::table('log_items')->insert($log_item_data);
			}
            // if new exercise set if it a time based exercise
            if ($exercise_is_new && $exercise_is_time == 1)
            {
                DB::table('exercises')->where('exercise_id', $exercise_id)->update(['is_time' => 1]);
            }
			// insert into log_exercises
            DB::table('log_exercises')
                ->where('logex_id', $log_exercises_id)
                ->update([
                    'logex_1rm' => $max_estimate_rm,
                    'logex_volume' => $total_volume,
                    'logex_reps' => $total_reps,
                    'logex_sets' => $total_sets,
                    'logex_warmup_volume' => $logex_warmup_volume,
                    'logex_warmup_reps' => $logex_warmup_reps,
                    'logex_warmup_sets' => $logex_warmup_sets,
                ]);
            $log_total_volume += $total_volume;
            $log_total_reps += $total_reps;
            $log_total_sets += $total_sets;
		}
        // insert total volumes
        DB::table('logs')
            ->where('log_id', $log_id)
            ->update([
                'log_total_volume' => $log_total_volume,
                'log_failed_volume' => $log_failed_volume,
                'log_total_reps' => $log_total_reps,
                'log_total_sets' => $log_total_sets,
                'log_warmup_volume' => $log_warmup_volume,
                'log_warmup_reps' => $log_warmup_reps,
                'log_warmup_sets' => $log_warmup_sets,
            ]);

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
			if ($this->user->user_unit == 'lb' && $input[1] == '')
			{
				return correct_weight($input[0], 'lb', 1);
			}
			elseif ($this->user->user_unit == 'kg' && $input[1] == '')
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
		// get old weight
        $old_weight = DB::table('logs')
                        ->where('log_date', '<', $log_date)
                        ->where('user_id', $user_id)
                        ->orderBy('log_date', 'desc')
                        ->value('log_weight');

		// update log entries with new weight
        DB::table('logs')
            ->where('log_date', '>', $log_date)
            ->where('user_id', $user_id)
            ->where('log_weight', $old_weight)
            ->update(['log_weight' => $user_weight]);
	}

	private function update_prs($user_id, $log_date, $exercise_id, $set_weight, $set_reps, $is_time)
	{
		// dont log reps over 10
		if ($set_reps > 10 || $set_reps < 1)
        {
			return false;
        }

        $old_1rm = DB::table('exercise_records')
                    ->where('user_id', $user_id)
                    ->where('pr_date', '<', $log_date)
                    ->where('exercise_id', $exercise_id)
                    ->where('is_time', $is_time)
                    ->orderBy('pr_date', 'desc')
                    ->value('pr_1rm');
        $new_1rm = $this->generate_rm ($set_weight, $set_reps);
        $is_est1rm = ($old_1rm < $new_1rm) ? true : false;

		// delete future logs that have lower prs
        DB::table('exercise_records')
            ->where('user_id', $user_id)
            ->where('pr_date', '>=', $log_date)
            ->where('exercise_id', $exercise_id)
            ->where('pr_reps', $set_reps)
            ->where(function($query) use ($is_time, $set_weight){
                if ($is_time == 0)
                {
                    $query->where('pr_value', '<=', $set_weight)
                            ->where('is_time', 0);
                }
                else
                {
                    $query->where('pr_value', '>=', $set_weight)
                            ->where('is_time', 1);
                }
            })
            ->delete();

        if ($is_est1rm)
        {
            // reset newer is_est1rm flags if they are now invalid
            DB::table('exercise_records')
                ->where('user_id', $user_id)
                ->where('pr_date', '>=', $log_date)
                ->where('exercise_id', $exercise_id)
                ->where(function($query) use ($is_time, $set_weight){
                    if ($is_time == 0)
                    {
                        $query->where('pr_1rm', '<=', $set_weight)
                                ->where('is_time', 0);
                    }
                    else
                    {
                        $query->where('pr_1rm', '>=', $set_weight)
                                ->where('is_time', 1);
                    }
                })
                ->update(['is_est1rm' => 0]);
        }

		// insert new entry
        DB::table('exercise_records')
            ->insert([
                'exercise_id' => $exercise_id,
                'user_id' => $user_id,
                'pr_date' => $log_date,
                'pr_value' => $set_weight,
                'pr_reps' => $set_reps,
                'pr_1rm' => $new_1rm,
                'is_est1rm' => $is_est1rm,
                'is_time' => $is_time
            ]);

        DB::table('log_items')
            ->where('user_id', $user_id)
            ->where('logitem_date', '>', $log_date)
            ->where('exercise_id', $exercise_id)
            ->where('logitem_reps', $set_reps)
            ->where(function($query) use ($is_time, $set_weight){
                if ($is_time == 0)
                {
                    $query->where('logitem_abs_weight', '<=', $set_weight)
                            ->where('is_time', 0);
                }
                else
                {
                    $query->where('logitem_abs_weight', '>=', $set_weight)
                            ->where('is_time', 1);
                }
            })
            ->update(['is_pr' => 0]);

		// add past prs if needed
        $sets = DB::table('log_items')
                    ->select('log_id', 'logitem_date', 'logitem_abs_weight')
                    ->where('user_id', $user_id)
                    ->where('logitem_date', '<', $log_date)
                    ->where('exercise_id', $exercise_id)
                    ->where('logitem_reps', $set_reps)
                    ->where(function($query) use ($is_time, $set_weight){
                        if ($is_time == 0)
                        {
                            $query->where('logitem_abs_weight', '>', $set_weight)
                                    ->where('is_time', 0);
                        }
                        else
                        {
                            $query->where('logitem_abs_weight', '<', $set_weight)
                                    ->where('is_time', 1);
                        }
                    })
                    ->where('is_pr', 0)
                    ->get();
		foreach ($sets as $set)
		{
			// update is_pr flag
            DB::table('log_items')
                ->where('log_id', $set['log_id'])
                ->update(['is_pr' => 1]);
            // get old est 1rm data
            $old_1rm = DB::table('exercise_records')
                        ->where('user_id', $user_id)
                        ->where('pr_date', '<', $set['log_date'])
                        ->where('exercise_id', $exercise_id)
                        ->where('is_time', $is_time)
                        ->orderBy('pr_date', 'desc')
                        ->value('pr_1rm');
            $new_1rm = $this->generate_rm ($set['logitem_abs_weight'], $set_reps);
            $is_est1rm = ($old_1rm < $new_1rm) ? true : false;
			// insert pr data
            DB::table('exercise_records')
                ->insert([
                    'exercise_id' => $exercise_id,
                    'user_id' => $user_id,
                    'pr_date' => $log_date,
                    'pr_value' => $set['logitem_abs_weight'],
                    'pr_reps' => $set_reps,
                    'pr_1rm' => $new_1rm,
                    'is_est1rm' => $is_est1rm,
                    'is_time' => $is_time
                ]);
		}
	}

    private function construct_globals ()
    {
        // pre-defined data
        $this->units = array(
            'T' => array(
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
                's' => 's', // needs to be at the end or it ruins the party
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
                if ($format_string == $format_dump)
                {
                    return true;
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

	private function special_tags($string)
	{
		$string = strtolower($string);
		// line is warmup
		if ($string == 'w' || $string == 'warmup' || $string == 'warm-up' || $string == 'warm up' || $string == 'wu')
		{
			return 'w';
		}
		return 0;
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
        // is number
        if (is_numeric($chr))
        {
            $output_chr = '0';
        }
        $output_chr = strtolower($output_chr);
        // is x
        if ($chr == '*' || $chr == '×')
        {
            $output_chr = 'x';
        }
        return $output_chr;
    }

    public function rebuild_log_text($user_id, $log_date)
	{
		// get the log data
        $log_data = Log::getlog($user_id, $log_date);
		$log_text = ''; // set that variable !!
        $user = User::find($user_id);
		foreach ($log_data->log_exercises() as $log_items)
		{
			$log_text .= "#" . ucwords($log_items['exercise']) . "\n"; // set exersice name
			foreach ($log_items->log_items() as $set)
			{
                if ($set['is_time'] == 1)
                {
                    $time = explode('.', $set['logitem_time']);
                    $decimaltime = (isset($time[1]) && $time[1] > 0) ? '.' . $time[1] : '';
                    $setvalue = Carbon::now()->timestamp($time[0])->toTimeString() . $decimaltime;
                }
                else
                {
    				// get user units
    				if ($set['is_bw'] == 0)
    				{
    					$setvalue = $set['logitem_weight'] . ' ' . $user->user_unit;
    				}
    				else
    				{
    					if ($set['logitem_weight'] != 0)
    					{
    						$setvalue = 'BW' . $set['logitem_weight'] . ' ' . $user->user_unit;
    					}
    					else
    					{
    						$setvalue = 'BW';
    					}
    				}
                }
				$pre = (!empty($set['logitem_pre'])) ? " @{$set['logitem_pre']}" : '';
                $type_key = ($set['is_warmup']) ? ' warmup' : '';
				$log_text .= "$setvalue x {$set['logitem_reps']} x {$set['logitem_sets']}$pre " . trim($set['logitem_comment']) . $type_key . "\n"; // add sets
			}
			if (strlen(trim($log_items['comment'])) > 0)
				$log_text .= "\n" . trim($log_items['comment']) . "\n"; // set comment
			$log_text .= "\n";
		}
		$log_text = rtrim($log_text);
        // insert the new log text
        Log::where('user_id', $user_id)
            ->where('log_date', $log_date)
            ->update(['log_text' => $log_text, 'log_update_text' => 0]);
		return $log_text;
	}

    public function generate_rm ($weight, $reps, $rm = 1)
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

    public function get_input_weight($weight_input, $log_date)
    {
        if (strlen($weight_input) == 0 || intval($weight_input) == 0)
		{
            $query = DB::table('logs')
                        ->where('log_date', '<', $log_date)
                        ->where('user_id', Auth::user()->user_id)
                        ->where('log_weight', '>', 0)
                        ->orderBy('log_date', 'desc');
			if ($query->count() > 0)
			{
				$weight = $query->value('log_weight');
			}
			else
			{
				$weight = Auth::user()->user_weight;
			}
		}
		else
		{
			$weight = floatval($weight_input);
		}
        return $weight;
    }

    private function flag_error($error)
    {
        echo $error;
    }
}
