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

class Tester
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
    // formatted data
    private $log;
    private $new_prs = [];
    private $log_exercises = [];
    private $log_items = [];
    private $exercises = [];

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

    public function parseText ()
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
                $this->parseLine ($line, $position);
            }
            else
            {
                $this->log_data['exercises'][$position]['comment'] .= $line . "\n";
            }
        }
    }

    private function parseLine ($line, $position)
    {
        // build the initial startup data
        $this->current_blocks = array('U', 'W', 'T', 'C');
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
            $format_chr = $this->format_character ($chr);
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

    public function saveLogData ()
    {
        // clear old entries
        DB::table('log_items')->where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->delete();
        DB::table('log_exercises')->where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->delete();

        // update user weight
        if ($this->log_date == Carbon::now()->format('Y-m-d'))
        {
            DB::table('users')
                ->where('user_id', $this->user->user_id)
                ->update(['user_weight' => $this->user_weight]);
        }
        else
        {
            // update future logs
            $this->updateUserBodyweight ();
        }

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
            //array_walk($this->log_items[$i], array($this, 'updateLogId'), $log_id);
            $this->log_exercises[$i]->log_items()->saveMany($this->log_items[$i]);
            unset($this->log_exercises[$i]);
            unset($this->log_items[$i]);
        }

        // as array_walk doesn't work :(
        DB::table('log_items')->where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->update(['log_id' => $log_id]);
    }

    private function updateLogId (&$log_item, $key, $log_id)
    {
        $log_item[$key]->log_id = $log_id;
    }

    public function formatLogData ($is_new)
    {
        // get old records if are any
        $old_records_raw = Exercise_record::select(DB::raw('MAX(pr_value) as pr_value'), 'pr_reps', 'exercise_id')
                                    ->where('log_date', $this->log_date)
                                    ->where('user_id', $this->user->user_id)
                                    ->groupBy('exercise_id')
                                    ->groupBy('pr_reps')
                                    ->get()
                                    ->toArray();
        $old_records = [];
        if (count($old_records_raw) > 0)
        {
            foreach ($old_records_raw as $record)
            {
                $old_records[$record['exercise_id']][$record['pr_reps']] = $record['pr_value'];
            }
        }
        Exercise_record::where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->delete();

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

            $max_estimate_rm = Exercise_record::getlastest1rm($this->user->user_id, $exercise_name)->value('pr_1rm');
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
                    $this->updatePrs ($old_records, $set, $i, $j);
                    if (!isset($this->new_prs[$exercise_name]))
                    {
                        $this->new_prs[$exercise_name] = array('W' => [], 'T' => [], 'E' => []);
                    }
                    $this->new_prs[$exercise_name][$pr_type][$set['R']][] = $this->log_items[$i][$j]->logitem_abs_weight;
                    $prs[$pr_type][$set['R']] = $this->log_items[$i][$j]->logitem_abs_weight;
                }
                if ($this->exercises[$i]['new'])
                {
                    $this->exercises[$i]['time'] = $this->log_items[$i][$j]->is_time;
                    $this->exercises[$i]['endurance'] = $this->log_items[$i][$j]->is_endurance;
                }
                $this->log_items[$i][$j]->logitem_1rm = $this->generate_rm ($this->log_items[$i][$j]->logitem_abs_weight, $set['R']);
                // get estimate 1rm
                if ($max_estimate_rm < $this->log_items[$i][$j]->logitem_1rm)
                {
                    $max_estimate_rm = $this->log_items[$i][$j]->logitem_1rm;
                }
                // fill in remaining values
                $this->log_items[$i][$j]->log_date = $this->log_date;
                $this->log_items[$i][$j]->user_id = $this->user->user_id;
                $this->log_items[$i][$j]->exercise_id = $this->exercises[$i]['id'];
                $this->insertLogItemWeightTime ($set, $i, $j);
                $this->log_items[$i][$j]->logitem_reps = $set['R'];
                $this->log_items[$i][$j]->logitem_sets = $set['S'];
                $this->log_items[$i][$j]->logitem_order = $j;
                $this->log_items[$i][$j]->logex_order = $i;
                $this->log_items[$i][$j]->logitem_pre = (!isset($set['P']) || $set['P'] == NULL) ? NULL : $set['P'];
            }
            $this->log_exercises[$i]->logex_1rm = $max_estimate_rm;
        }

        //return your new records :)
        if (count($this->new_prs) > 0)
        {
            Session::flash('new_prs', $this->new_prs);
        }
    }

    private function insertLogItemWeightTime ($set, $i, $j)
    {
        if ($this->log_items[$i][$j]->is_time)
        {
            if ($this->log_items[$i][$j]->logitem_time == 0 && $this->log_items[$i][$j]->logitem_weight > 0)
            {
                $this->log_items[$i][$j]->logitem_time = $set['W'];
            }
            else
            {
                $this->log_items[$i][$j]->logitem_time = $set['T'];
            }
            $this->log_items[$i][$j]->logitem_weight = 0;
        }
        else
        {
            if ($this->log_items[$i][$j]->logitem_time > 0 && $this->log_items[$i][$j]->logitem_weight == 0)
            {
                $this->log_items[$i][$j]->logitem_weight = $set['T'];
            }
            else
            {
                $this->log_items[$i][$j]->logitem_weight = $set['W'];
            }
            $this->log_items[$i][$j]->logitem_time = 0;
        }
    }

    private function correctUnitsDatabase ($input, $type)
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
        else
        {
            return $input;
        }
    }

    private function updateUserBodyweight ()
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

    public function cleanSetData ($set, $i, $j)
    {
        if (isset($set['U']))
        {
            if ($this->exercises[$i]['time'])
            {
                $set['T'][0] = floatval($set['U']);
                $set['T'][1] = '';
            }
            else
            {
                $set['W'][0] = floatval($set['U']);
                $set['W'][1] = '';
            }
            unset($set['U']);
        }
        if (isset($set['W']))
        {
            $set['W'][0] = str_replace(' ', '', $set['W'][0]);
            if (strtoupper(substr($set['W'][0], 0, 2)) == 'BW')
            {
                $this->log_items[$i][$j]->is_bw = true;
                $set['W'][0] = substr($set['W'][0], 2);
            }
            $set['W'] = $this->correctUnitsDatabase ($set['W'], 'W');
            $set['T'] = 0;
        }
        elseif (isset($set['T']))
        {
            $set['T'][0] = str_replace(' ', '', $set['T'][0]);
            $set['T'] = $this->correctUnitsDatabase ($set['T'], 'T');
            $this->log_items[$i][$j]->is_time = true;
            $set['W'] = 0;
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
                    elseif ($this->isEnduranceTag ($part))
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
        if ($this->log_items[$i][$j]->is_time)
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
            if (!isset($prs['E'][$set['R']]) ||
                floatval($prs['E'][$set['R']]) < $this->log_items[$i][$j]->logitem_abs_weight)
            {
                return true;
            }
        }
        elseif ($this->log_items[$i][$j]->is_time)
        {
            // time
            if (!isset($prs['T'][$set['R']]) ||
                floatval($prs['T'][$set['R']]) > $this->log_items[$i][$j]->logitem_abs_weight)
            {
                return true;
            }
        }
        else
        {
            // weight
            if (!isset($prs['W'][$set['R']]) ||
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

    private function updatePrs ($old_records, $set, $i, $j)
    {
        $exercise_id = $this->exercises[$i]['id'];
        $set_weight = $this->log_items[$i][$j]->logitem_abs_weight;
        $set_reps = $set['R'];
        $is_time = $this->log_items[$i][$j]->is_time;
        $is_endurance = $this->log_items[$i][$j]->is_endurance;

        // dont log reps over 10
        if ($set_reps > 10 || $set_reps < 1)
        {
            return false;
        }

        // check if set a new est 1 RM
        $old_1rm = DB::table('exercise_records')
                    ->where('user_id', $this->user->user_id)
                    ->where('log_date', '<', $this->log_date)
                    ->where('exercise_id', $exercise_id)
                    ->where('is_est1rm', 1)
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

        // unset lower future PRs
        DB::table('log_items')
            ->where('user_id', $this->user->user_id)
            ->where('log_date', '>=', $this->log_date)
            ->where('exercise_id', $exercise_id)
            ->where('logitem_reps', $set_reps)
            ->where(function($query) use ($is_time, $is_endurance, $set_weight){
                if ($is_time == 1 && $is_endurance == 0)
                {
                    $query->where('logitem_abs_weight', '>=', $set_weight);
                }
                else
                {
                    $query->where('logitem_abs_weight', '<=', $set_weight);
                }
            })
            ->where('is_time', $is_time)
            ->where('is_endurance', $is_endurance)
            ->update(['is_pr' => 0]);

        // we are modifying a PR that was set today
        if (isset($old_records[$exercise_id][$set_reps]))
        {
            $old_pr_value = $old_records[$exercise_id][$set_reps];
            // add future prs if needed
            $sets = DB::table('log_items')
                        ->select('log_id', 'log_date', 'logitem_abs_weight')
                        ->where('user_id', $this->user->user_id)
                        ->where('log_date', '>', $this->log_date)
                        ->where('exercise_id', $exercise_id)
                        ->where('logitem_reps', $set_reps)
                        ->where(function($query) use ($is_time, $is_endurance, $set_weight, $old_pr_value){
                            if ($is_time == 1 && $is_endurance == 0)
                            {
                                $query->where('logitem_abs_weight', '<', $set_weight)
                                    ->where('logitem_abs_weight', '>=', $old_pr_value);
                            }
                            else
                            {
                                $query->where('logitem_abs_weight', '>', $set_weight)
                                    ->where('logitem_abs_weight', '<=', $old_pr_value);
                            }
                        })
                        ->where('is_time', $is_time)
                        ->where('is_endurance', $is_endurance)
                        ->where('is_pr', 0)
                        ->orderBy('log_date', 'asc')
                        ->get();
            foreach ($sets as $set)
            {
                // update is_pr flag
                DB::table('log_items')
                    ->where('log_id', $set->log_id)
                    ->update(['is_pr' => 1]);

                // check if new 1rm has been set
                $new_1rm = $this->generate_rm ($set->logitem_abs_weight, $set_reps);
                $is_est1rm = (($old_1rm < $new_1rm) || ($is_time == 1 && $is_endurance == 0 && $old_1rm > $new_1rm)) ? true : false;
                if ($is_est1rm)
                {
                    $old_1rm = $new_1rm;
                }
                // insert pr data
                Exercise_record::create([
                    'exercise_id' => $exercise_id,
                    'user_id' => $this->user->user_id,
                    'log_date' => $set->log_date,
                    'pr_value' => $set->logitem_abs_weight,
                    'pr_reps' => $set_reps,
                    'pr_1rm' => $new_1rm,
                    'is_est1rm' => $is_est1rm,
                    'is_time' => $is_time,
                    'is_endurance' => $is_endurance
                ]);
            }
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
            'is_time' => $is_time,
            'is_endurance' => $is_endurance
        ]);
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
            'U' => array(('0')),
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
            'U' => array('R', 'P'),
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
        if (isset($this->format_dump[strlen($this->format_dump) - 1]) && $this->format_dump[strlen($this->format_dump) - 1] == '0' && $format_chr == '0')
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

    public function getUserWeight ()
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
