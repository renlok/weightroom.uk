<?php

namespace App\Extend;

use Auth;
use Carbon;
use DB;
use Session;
use App\Log;
use App\Log_item;
use App\Log_exercise;
use App\User;
use App\Exercise;
use App\Exercise_record;
use App\Extend\Format;

class Parser
{
    // the predefined data variables
    private $units; // array of units for each block type
    private $format_types_all; // all possible formats for all possible blocks
    private $next_values_all; // flags that show a certain type of block is coming up
    private $format_follows; // what should come after each block type
    private $user; // load User class
    // the working variables
    private $accepted_char;
    private $accepted_chars;
    private $format_type;
    private $current_blocks; // the blocks that are expected
    private $next_values;
    // data dumps
    private $format_dump;
    private $chunk_dump;
    // final array
    private $log_data;
    private $log_text;
    // useful data for saving the log
    private $user_weight;
    private $log_date;
    // testing data
    private $temp_data;

    public function __construct($log_text, $log_date, $user_weight)
    {
        // load the user
        $this->user = Auth::user();
        // build the initial startup data
        $this->log_text = $log_text;
        $this->log_date = $log_date;
        $this->user_weight = $user_weight;
        $this->construct_globals ();
        $this->getUserWeight ();
    }

    public function parse_text ()
    {
        $exercise = '';
        $position = -1; // a pointer for when exercise was done
        $this->log_data = array('comment' => '', 'exercises' => array()); // the output array
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
					$this->log_data['comment'] .= "\n";
				}
				$this->log_data['comment'] .= $line;
				continue; // end this loop
			}
			elseif (is_numeric($line[0]) || $line[0] == 'B')
			{
                $this->parse_line ($line, $position);
			}
			else
			{
				$this->log_data['exercises'][$position]['comment'] .= $line . "\n";
			}
        }
    }

    private function parse_line ($line, $position)
    {
        // build the initial startup data
        $this->current_blocks = array('W', 'T', 'C');
        $this->build_next_formats ();
        $this->build_accepted_char ();
        $this->build_accepted_chars ();
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
            if (count($this->accepted_chars) == 0 || (in_array($format_chr, $this->accepted_chars) && $this->check_keeps_format($format_chr)))
            {
                $this->build_accepted_char ();
                $this->build_accepted_chars ($format_chr);
                if (is_numeric($chr))
                {
                    // check the last value of the format_dump if its not 0 already set it to 0
                    if (!isset($this->format_dump[strlen($this->format_dump) - 1]) || $this->format_dump[strlen($this->format_dump) - 1] != '0')
                    {
                        $this->format_dump .= '0';
                    }
                }
                else
                {
                    // the character is not a number so just add it to the format
                    $this->format_dump .= $format_chr;
                }
                $this->chunk_dump .= $chr;
            }
            else
            {
                // check the previous chunk is valid
                if ($this->format_check())
                {
    				// the current chunk has finshed do something
    				$output_data[$multiline][$this->current_blocks[0]] = $this->clean_units(trim($this->chunk_dump), $this->current_blocks[0]);
    				// reset the dumps
    				$this->format_dump = '';
    				$this->chunk_dump = '';
                    // we are repeating the chunk
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
                    // the chuck is no longer valid assumes its a comment
                    $this->chunk_dump .= $chr;
                    $this->current_blocks[0] = 'C';
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

		// clean up the given data
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

        // check isn't just a comment
        if (isset($output_data[$multiline_max]['C']) && !empty($output_data[$multiline_max]['C']) && count($output_data[$multiline_max]) == 1)
        {
            $this->log_data['exercises'][$position]['comment'] .= $output_data[$multiline_max]['C'] . "\n";
            unset($output_data[$multiline_max]);
        }

        if (count($output_data) > 0)
        {
            // update log_data variable
            $this->log_data['exercises'][$position]['data'] = array_merge($this->log_data['exercises'][$position]['data'], $output_data);
        }
    }

	public function store_log_data ($is_new)
	{
		// clear old entries
        DB::table('log_items')->where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->delete();
        DB::table('log_exercises')->where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->delete();
        DB::table('exercise_records')->where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->delete();

		// delete log and exit function if no data
		if (count($this->log_data) <= 1)
		{
            DB::table('logs')->where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->delete();
			return false;
		}

		//check if its new
		if (!$is_new)
		{
			// update log entry
            $log_id = DB::table('logs')
                    ->where('log_date', $this->log_date)
                    ->where('user_id', $this->user->user_id)
                    ->value('log_id');
            DB::table('logs')
                ->where('log_date', $this->log_date)
                ->where('user_id', $this->user->user_id)
                ->update([
                    'log_text' => $this->log_text,
                    'log_comment' => trim($this->log_data['comment']),
                    'log_weight' => $this->user_weight
                ]);
		}
		else
		{
            // just incase lets run a delete
            DB::table('logs')
                ->where('log_date', $this->log_date)
                ->where('user_id', $this->user->user_id)
                ->delete();
			// add a new entry
            $log = Log::create([
                'log_text' => $this->log_text,
                'log_comment' => trim($this->log_data['comment']),
                'log_weight' => $this->user_weight,
                'log_date' => $this->log_date,
                'user_id' => $this->user->user_id
            ]);
            $log_id = $log->log_id;
		}
        // values that must be updated later
        $log_warmup_volume = $log_warmup_reps = $log_warmup_sets = $log_total_volume = $log_failed_volume = $log_failed_sets = $log_total_reps = $log_total_sets = 0;
		// todays log then update weight
		if ($this->log_date == date("Y-m-d"))
		{
            DB::table('users')
                ->where('user_id', $this->user->user_id)
                ->update(['user_weight' => $this->user_weight]);
		}
		else
		{
			// update future logs
			$this->update_user_weights ();
		}

		$new_prs = array();
		// add all of the exercise details
		for ($i = 0, $count_i = count($this->log_data['exercises']); $i < $count_i; $i++)
		{
			$item = $this->log_data['exercises'][$i];
			// set exercise name
			$exercise_name = trim($item['name']);
			// reset totals
			$total_volume = $failed_volume = $failed_sets = $total_reps = $total_sets = 0;
            $logex_warmup_volume = $logex_warmup_reps = $logex_warmup_sets = 0;
            // get exercise information
            $exercise = Exercise::getexercise($exercise_name, $this->user->user_id)->first();
            // new exercise
            if ($exercise == null)
            {
                $exercise_is_new = true;
                $exercise = Exercise::create([
                    'exercise_name' => $exercise_name,
                    'user_id' => $this->user->user_id
                ]);
                $exercise_id = $exercise->exercise_id;
                $exercise_is_endurance = $exercise_is_time = false; // assume this for now
            }
            else
            {
                $exercise_is_new = false;
                $exercise_id = $exercise->exercise_id;
                $exercise_is_time = $exercise->is_time;
                $exercise_is_endurance = $exercise->is_endurance;
            }
            // insert log_exercise
            $log_exercise = Log_exercise::create([
                'log_date' => $this->log_date,
                'log_id' => $log_id,
                'user_id' => $this->user->user_id,
                'exercise_id' => $exercise_id,
                'logex_comment' => trim($item['comment']),
                'logex_order' => $i
            ]);
            $log_exercises_id = $log_exercise->logex_id;
            $prs = [];
            $prs['E'] = Exercise_record::getexerciseprs($this->user->user_id, $this->log_date, $exercise_name, true, true)->toArray();
            $prs['T'] = Exercise_record::getexerciseprs($this->user->user_id, $this->log_date, $exercise_name, true, false)->toArray();
            $prs['W'] = Exercise_record::getexerciseprs($this->user->user_id, $this->log_date, $exercise_name, false, false)->toArray();
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
				$set['R'] = (isset($set['R'])) ? intval($set['R']) : 1;
				$set['S'] = (isset($set['S'])) ? intval($set['S']) : 1;
				$set['C'] = (isset($set['C'])) ? $set['C'] : '';
                $is_warmup = false;
                $is_endurance = $exercise_is_endurance;
                // check if the comment is a special tag
                $options = $this->special_tags($set['C']);
                if($options != 0 || is_array($options))
                {
                    $set['C'] = '';
                    foreach ($options as $k => $v)
                    {
                        if ($v == 'w')
                        {
                            $is_warmup = true;
                        }
                        elseif ($v == 'e')
                        {
                            $is_endurance = true;
                        }
                        else
                        {
                            $set['C'] .= $v;
                        }
                    }
                }
				$absolute_weight = ($is_bw == false) ? $set['W'] : ($set['W'] + $this->user_weight);
                $item_volume = ($absolute_weight * $set['R'] * $set['S']);
				$total_volume += $item_volume;
                if ($set['R'] == 0)
                {
                    $failed_volume += ($absolute_weight * $set['S']);
                    $failed_sets += $set['S'];
                }
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
                if ((($is_time && !$is_endurance && (!isset($prs['T'][$set['R']]) || floatval($prs['T'][$set['R']]) > floatval($absolute_weight))) || // set time PR
                    ($is_time && $is_endurance && (!isset($prs['E'][$set['R']]) || floatval($prs['E'][$set['R']]) < floatval($absolute_weight))) || // set endurance PR
                    (!$is_time && (!isset($prs['W'][$set['R']]) || floatval($prs['W'][$set['R']]) < floatval($absolute_weight)))) && // set weight PR
                    $set['R'] != 0)
				{
					$is_pr = true;
                    $pr_type = ($is_time == true) ? (($is_endurance == true) ? 'E' : 'T') : 'W';
                    // the user has set a pr we need to add/update it in the database
                    $this->update_prs ($exercise_id, $absolute_weight, $set['R'], $is_time, $is_endurance);
                    if (!isset($new_prs[$exercise_name]))
                        $new_prs[$exercise_name] = array('W' => array(), 'T' => array());
                    $new_prs[$exercise_name][$pr_type][$set['R']][] = $absolute_weight;
                    // update pr array
                    $prs[$pr_type][$set['R']] = $absolute_weight;
				}
                if ($exercise_is_new)
                {
                    $exercise_is_time = $is_time;
                    $exercise_is_endurance = $is_endurance;
                }
				$estimate_rm = $this->generate_rm ($absolute_weight, $set['R']);
				// get estimate 1rm
				if ($max_estimate_rm < $estimate_rm)
				{
					$max_estimate_rm = $estimate_rm;
				}
				// insert into log_items
                $log_item_data = [
                    'log_date' => $this->log_date,
                    'log_id' => $log_id,
                    'logex_id' => $log_exercises_id,
                    'user_id' => $this->user->user_id,
                    'exercise_id' => $exercise_id,
                    'logitem_weight' => $set['W'],
                    'logitem_abs_weight' => $absolute_weight,
                    'logitem_time' => $set['T'],
                    'logitem_reps' => $set['R'],
                    'logitem_sets' => $set['S'],
                    'logitem_comment' => trim($set['C']),
                    'logitem_1rm' => $estimate_rm,
                    'is_pr' => $is_pr,
                    'is_bw' => $is_bw,
                    'is_time' => $is_time,
                    'is_warmup' => $is_warmup,
                    'is_endurance' => $is_endurance,
                    'logitem_order' => $j,
                    'logex_order' => $i,
                ];
				if (!isset($set['P']) || $set['P'] == NULL)
					$log_item_data['logitem_pre'] = NULL;
				else
					$log_item_data['logitem_pre'] = $set['P'];
                Log_item::create($log_item_data);
			}
            // if new exercise set if it a time based exercise
            if ($exercise_is_new)
            {
                $update_exercises = [];
                if ($exercise_is_time)
                {
                    $update_exercises['is_time'] = 1;
                }
                if ($exercise_is_endurance)
                {
                    $update_exercises['is_endurance'] = 1;
                }
                if (count($update_exercises) > 0)
                {
                    DB::table('exercises')->where('exercise_id', $exercise_id)->update($update_exercises);
                }
            }
			// insert into log_exercises
            DB::table('log_exercises')
                ->where('logex_id', $log_exercises_id)
                ->update([
                    'logex_1rm' => $max_estimate_rm,
                    'logex_volume' => $total_volume,
                    'logex_reps' => $total_reps,
                    'logex_sets' => $total_sets,
                    'logex_failed_volume' => $failed_volume,
                    'logex_failed_sets' => $failed_sets,
                    'logex_warmup_volume' => $logex_warmup_volume,
                    'logex_warmup_reps' => $logex_warmup_reps,
                    'logex_warmup_sets' => $logex_warmup_sets,
                ]);
            $log_total_volume += $total_volume;
            $log_total_reps += $total_reps;
            $log_total_sets += $total_sets;
            $log_failed_volume += $failed_volume;
            $log_failed_sets += $failed_sets;
		}
        // insert total volumes
        DB::table('logs')
            ->where('log_id', $log_id)
            ->update([
                'log_total_volume' => $log_total_volume,
                'log_total_reps' => $log_total_reps,
                'log_total_sets' => $log_total_sets,
                'log_failed_volume' => $log_failed_volume,
                'log_failed_sets' => $log_failed_sets,
                'log_warmup_volume' => $log_warmup_volume,
                'log_warmup_reps' => $log_warmup_reps,
                'log_warmup_sets' => $log_warmup_sets,
            ]);

		//return your new records :)
        if (count($new_prs) > 0)
        {
            Session::flash('new_prs', $new_prs);
        }
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
				return Format::correct_time($input[0], $input[1], 's', 0);
			}
		}
		elseif ($type == 'W')
		{
			// users default is lb
			if ($this->user->user_unit == 'lb' && $input[1] == '')
			{
				return Format::correct_weight($input[0], 'lb', 'kg', 0);
			}
			elseif ($this->user->user_unit == 'kg' && $input[1] == '')
			{
				return $input[0];
			}
			else
			{
				return Format::correct_weight($input[0], $input[1], 'kg', 0);
			}
		}
	}

	private function update_user_weights()
	{
		// get old weight
        $old_weight = DB::table('logs')
                        ->where('log_date', '<', $this->log_date)
                        ->where('user_id', $this->user->user_id)
                        ->orderBy('log_date', 'desc')
                        ->value('log_weight');

		// update log entries with new weight
        DB::table('logs')
            ->where('log_date', '>', $this->log_date)
            ->where('user_id', $this->user->user_id)
            ->where('log_weight', $old_weight)
            ->update(['log_weight' => $this->user_weight]);
	}

	private function update_prs($exercise_id, $set_weight, $set_reps, $is_time, $is_endurance)
	{
		// dont log reps over 10
		if ($set_reps > 10 || $set_reps < 1)
        {
			return false;
        }

        $old_1rm = DB::table('exercise_records')
                    ->where('user_id', $this->user->user_id)
                    ->where('log_date', '<', $this->log_date)
                    ->where('exercise_id', $exercise_id)
                    ->where('is_time', $is_time)
                    ->where('is_endurance', $is_endurance)
                    ->orderBy('log_date', 'desc')
                    ->value('pr_1rm');
        $new_1rm = $this->generate_rm ($set_weight, $set_reps);
        $is_est1rm = (($old_1rm < $new_1rm) || ($is_time == 1 && $is_endurance == 0 && $old_1rm > $new_1rm)) ? true : false;

		// delete future logs that have lower prs
        DB::table('exercise_records')
            ->where('user_id', $this->user->user_id)
            ->where('log_date', '>=', $this->log_date)
            ->where('exercise_id', $exercise_id)
            ->where('pr_reps', $set_reps)
            ->where(function($query) use ($is_time, $is_endurance, $set_weight){
                if ($is_time == 1 && $is_endurance == 0)
                {
                    $query->where('pr_value', '>=', $set_weight);
                }
                else
                {
                    $query->where('pr_value', '<=', $set_weight);
                }
            })
            ->where('is_time', $is_time)
            ->where('is_endurance', $is_endurance)
            ->delete();

        if ($is_est1rm)
        {
            // reset newer is_est1rm flags if they are now invalid
            DB::table('exercise_records')
                ->where('user_id', $this->user->user_id)
                ->where('log_date', '>=', $this->log_date)
                ->where('exercise_id', $exercise_id)
                ->where(function($query) use ($is_time, $is_endurance, $set_weight){
                    if ($is_time == 1 && $is_endurance == 0)
                    {
                        $query->where('pr_1rm', '>=', $set_weight);
                    }
                    else
                    {
                        $query->where('pr_value', '<=', $set_weight);
                    }
                })
                ->where('is_time', $is_time)
                ->where('is_endurance', $is_endurance)
                ->update(['is_est1rm' => 0]);
        }

		// insert new entry
        Exercise_record::create([
            'exercise_id' => $exercise_id,
            'user_id' => $this->user->user_id,
            'log_date' => $this->log_date,
            'pr_value' => $set_weight,
            'pr_reps' => $set_reps,
            'pr_1rm' => $new_1rm,
            'is_est1rm' => $is_est1rm,
            'is_time' => $is_time
        ]);

        DB::table('log_items')
            ->where('user_id', $this->user->user_id)
            ->where('log_date', '>', $this->log_date)
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
                    ->select('log_id', 'log_date', 'logitem_abs_weight')
                    ->where('user_id', $this->user->user_id)
                    ->where('log_date', '<', $this->log_date)
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
                ->where('log_id', $set->log_id)
                ->update(['is_pr' => 1]);
            // get old est 1rm data
            $old_1rm = DB::table('exercise_records')
                        ->where('user_id', $this->user->user_id)
                        ->where('log_date', '<', $set->log_date)
                        ->where('exercise_id', $exercise_id)
                        ->where('is_time', $is_time)
                        ->orderBy('log_date', 'desc')
                        ->value('pr_1rm');
            $new_1rm = $this->generate_rm ($set->logitem_abs_weight, $set_reps);
            $is_est1rm = ($old_1rm < $new_1rm) ? true : false;
			// insert pr data
            Exercise_record::create([
                'exercise_id' => $exercise_id,
                'user_id' => $this->user->user_id,
                'log_date' => $this->log_date,
                'pr_value' => $set->logitem_abs_weight,
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
                ('bw'),
                ('bw+0.0'),
                ('bw+0'),
                ('bw-0.0'),
                ('bw-0'),
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
        // comment can follow anything
        $this->format_follows = array(
            'T' => array('R', 'P'),
            'W' => array('R', 'P'),
            'R' => array('S', 'P'),
            'S' => array('P'),
            'P' => array(''),
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
                    // comments are always allowed
                    $this->current_blocks[] = 'C';
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

    // check end chunk is valid
    private function format_check()
    {
        // the block is a comment so skip the check
        if ($this->current_blocks[0] == 'C')
        {
            return true;
        }
        // check if the final format_dump matches a vlid format type
        foreach ($this->format_type as $sub_type)
        {
            foreach ($sub_type as $key => $format_string)
            {
                //foreach ($val as $format_string)
                if ($format_string == $this->format_dump)
                {
                    return true;
                }
            }
        }
        return false;
    }

    // check if next character keeps chunk valid
    private function check_keeps_format($format_chr)
    {
        // nothing significcant has been added
        if (isset($this->format_dump[strlen($this->format_dump) - 1]) && $this->format_dump[strlen($this->format_dump) - 1] == '0')
        {
            return true;
        }
        // check if the current format_dump matches a vlid format type
        $format_string = $this->format_dump . $format_chr;
        foreach ($this->format_type as $sub_type)
        {
            foreach($sub_type as $format)
            {
                if(stristr($format, $format_string) !== false)
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
                // TODO: fix trim(substr($block, $end)), $block set incorrectly
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
        if (empty($string))
        {
            return 0;
        }
		$string = trim(strtolower($string));
		// line is warmup
		if ($string == 'w' || $string == 'warmup' || $string == 'warm-up' || $string == 'warm up' || $string == 'wu')
		{
			return ['w'];
		}
        elseif ($string == 'e' || $string == 'endurance')
		{
			return ['e'];
		}
        $parts = explode('|', $string);
        if (count($parts) > 1)
        {
            $return = [];
            foreach ($parts as $part)
            {
                if ($part == 'w' || $part == 'warmup' || $part == 'warm-up' || $part == 'warm up' || $part == 'wu')
        		{
        			$return[] = 'w';
        		}
                elseif ($part == 'e' || $part == 'endurance')
        		{
                    $return[] = 'e';
        		}
                else
                {
                    $return[] = $part;
                }
            }
            return $return;
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

    public function getUserWeight()
    {
        if (strlen($this->user_weight) == 0 || intval($this->user_weight) == 0)
		{
            $query = DB::table('logs')
                        ->where('log_date', '<', $this->log_date)
                        ->where('user_id', $this->user->user_id)
                        ->where('log_weight', '>', 0)
                        ->orderBy('log_date', 'desc');
			if ($query->count() > 0)
			{
				$this->user_weight = $query->value('log_weight');
			}
			else
			{
				$this->user_weight = $this->user->user_weight;
			}
		}
		else
		{
			$this->user_weight = Format::correct_weight(floatval($this->user_weight), $this->user->user_unit, 'kg');
		}
    }

    private function flag_error($error)
    {
        echo $error;
    }
}
