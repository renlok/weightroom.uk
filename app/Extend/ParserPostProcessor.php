<?php

namespace App\Extend;

use Auth;
use Carbon;
use DB;
use Session;
use App\Log;
use App\Log_item;
use App\Log_exercise;
use App\Exercise;
use App\Exercise_goal;
use App\Exercise_group;
use App\Exercise_group_relation;
use App\Exercise_record;

class ParserPostProcessor extends ParserCore
{
    protected $user; // load User class
    // useful data for saving the log
    protected $user_weight;
    protected $log_date;
    // formatted data
    private $log;
    private $log_exercises = [];
    private $is_speed;
    private $log_items = [];
    private $exercises = [];
    // for flash messages
    private $new_prs = [];
    private $new_exercises = [];
    private $warnings = [];
    private $goals_hit = [];

    public function __construct($log_text, $log_date, $user_weight, $user = null)
    {
        parent::__construct($log_text);
        $this->log_date = $log_date;
        if ($user)
        {
            // load the user
            $this->user = $user;
            $this->getUserWeight ($user_weight);
        }
    }

    public function getUserWeight ($user_weight)
    {
        if (strlen($user_weight) == 0 || $user_weight == 0)
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
            $this->user_weight = Format::correct_weight($user_weight, $this->user->user_unit, 'kg', 0);
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
            $this->log_exercises[$i]->log_items()->saveMany($this->log_items[$i]);
            unset($this->log_exercises[$i]);
            unset($this->log_items[$i]);
        }

        // as array_walk doesn't work :(
        DB::table('log_items')->where('log_date', $this->log_date)->where('user_id', $this->user->user_id)->update(['log_id' => $log_id]);

        // update first log flag
        if ($this->user->user_firstlog)
        {
            if (trim($this->log_text) == '')
            {
                $this->warnings['no_text'] = true;
            }
            elseif (!isset($this->warnings['no_exercises']))
            {
                DB::table('users')->where('user_id', $this->user->user_id)->update(['user_firstlog' => 0]);
            }
        }
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
            $this->resetLogDefaults ();
        }
        else
        {
            // build a new log
            $this->log = new Log;
            $this->log->log_date = $this->log_date;
            $this->log->user_id = $this->user->user_id;
        }
        // set log values
        $this->log->log_update_text = $this->log_update_text;
        $this->log->log_text = trim($this->log_text);
        $this->log->log_comment = Format::replace_video_urls(Format::addUserLinks(nl2br(e(trim($this->log_data['comment']))), 'log', ['url_params' => ['log_date' => $this->log_date, 'user_name' => $this->user->user_name]]));
        $this->log->log_weight = $this->user_weight;

        // add all of the exercise details
        for ($i = 0, $count_i = count($this->log_data['exercises']); $i < $count_i; $i++)
        {
            $item = $this->log_data['exercises'][$i];
            // create new Log_exercise
            $this->log_exercises[$i] = new Log_exercise;
            $this->log_items[$i] = [];
            // set exercise name
            $exercise_name = $item['name'];

            // get exercise information
            $exercise = Exercise::getexercise($exercise_name, $this->user->user_id)->first();
            if ($exercise == null)
            {
                // new exercise
                $exercise = Exercise::create([
                    'exercise_name' => $exercise_name,
                    'exercise_name_clean' => Format::urlSafeString($exercise_name),
                    'user_id' => $this->user->user_id
                ]);
                $this->exercises[$i]['new'] = true;
                $this->exercises[$i]['time'] = false;
                $this->exercises[$i]['endurance'] = false;
                $this->exercises[$i]['distance'] = false;
                $goals = null;
            }
            else
            {
                $this->exercises[$i]['new'] = false;
                $this->exercises[$i]['time'] = $exercise->is_time;
                $this->exercises[$i]['endurance'] = $exercise->is_endurance;
                $this->exercises[$i]['distance'] = $exercise->is_distance;
                // load exercise goals
                $goals = Exercise_goal::where('exercise_id', $exercise->exercise_id)->where('goal_complete', false)->get();
            }
            if (isset($item['groups']) && count($item['groups']) > 0)
            {
                foreach ($item['groups'] as $exercise_group_name)
                {
                    $exercise_group = Exercise_group::where('user_id', Auth::user()->user_id)->where('exgroup_name', $exercise_group_name)->first();
                    if ($exercise_group == null) {
                        $exercise_group = new Exercise_group();
                        $exercise_group->user_id = Auth::user()->user_id;
                        $exercise_group->exgroup_name = $exercise_group_name;
                        $exercise_group->exgroup_name_clean = Format::urlSafeString($exercise_group_name);
                        $exercise_group->save();
                    }
                    Exercise_group_relation::firstOrCreate(['exgroup_id' => $exercise_group->exgroup_id, 'exercise_id' => $exercise->exercise_id]);
                }
            }
            $this->exercises[$i]['update'] = false;
            $this->exercises[$i]['id'] = $exercise->exercise_id;
            // insert log_exercise data
            $this->log_exercises[$i]->log_date = $this->log_date;
            $this->log_exercises[$i]->user_id = $this->user->user_id;
            $this->log_exercises[$i]->exercise_id = $this->exercises[$i]['id'];
            $this->log_exercises[$i]->logex_comment = Format::replace_video_urls(Format::addUserLinks(nl2br(e(trim($item['comment']))), 'log', ['url_params' => ['log_date' => $this->log_date, 'user_name' => $this->user->user_name]]));
            $this->log_exercises[$i]->logex_order = $i;

            $prs = Exercise_record::exercisePrs($this->user->user_id, $this->log_date, $exercise_name);

            $max_estimate_rm = 0;
            if (count($item['data']) == 0)
            {
                $this->warnings['blank_exercise'] = true;
            }
            // add set data to exercise
            for ($j = 0, $count_j = count($item['data']); $j < $count_j; $j++)
            {
                $set = $item['data'][$j];
                $this->log_items[$i][$j] = new Log_item;
                // guess what these should be from exercise data
                $this->log_items[$i][$j]->is_time = $this->exercises[$i]['time'];
                $this->log_items[$i][$j]->is_endurance = $this->exercises[$i]['endurance'];
                $this->log_items[$i][$j]->is_distance = $this->exercises[$i]['distance'];
                // check comment for special tags
                $set['C'] = (isset($set['C'])) ? trim($set['C']) : '';
                $this->checkSpecialTags ($set['C'], $i, $j);
                // clean up set data (+ converts units ready for db insertion)
                $set = $this->cleanSetData ($set, $i, $j);
                if ($this->log_items[$i][$j]->is_bw && $this->user_weight == 0)
                {
                    $this->warnings['blank_bodyweight'] = true;
                }
                $this->setAbsoluteWeight ($set, $i, $j);
                // check its a pr
                if ($this->checkPR ($prs, $set, $i, $j))
                {
                    $this->log_items[$i][$j]->is_pr = true;
                    $pr_type = ($this->log_items[$i][$j]->is_distance == true) ? 'D' :
                        (($this->log_items[$i][$j]->is_endurance == true) ? 'E' :
                            (($this->log_items[$i][$j]->is_time == true) ? 'T' : 'W'));
                    // the user has set a pr we need to add/update it in the database
                    $this->updatePrs ($old_records, $set, $i, $j);
                    $prs[$pr_type][$set['R']] = $this->log_items[$i][$j]->logitem_abs_weight;
                    // dont give PR message if new exercise
                    if (!($this->exercises[$i]['new'] || $this->exercises[$i]['update']))
                    {
                        if (!isset($this->new_prs[$exercise_name]))
                        {
                            $this->new_prs[$exercise_name] = array('W' => [], 'T' => [], 'E' => [], 'D' => []);
                        }
                        $this->new_prs[$exercise_name][$pr_type][$set['R']][] = $this->log_items[$i][$j]->logitem_abs_weight;
                    }
                }
                if ($this->exercises[$i]['new'])
                {
                    $this->exercises[$i]['new'] = false;
                    $this->exercises[$i]['update'] = true;
                    $this->exercises[$i]['time'] = $this->log_items[$i][$j]->is_time;
                    $this->exercises[$i]['endurance'] = $this->log_items[$i][$j]->is_endurance;
                    $this->exercises[$i]['distance'] = $this->log_items[$i][$j]->is_distance;
                    $this->new_exercises[] = [$exercise_name, $this->exercises[$i]['time'], $this->exercises[$i]['endurance'], $this->exercises[$i]['distance']];
                }
                $this->log_items[$i][$j]->logitem_1rm = PRs::generateRM ($this->log_items[$i][$j]->logitem_abs_weight, $set['R']);
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
                $this->setExerciseDefaultTypes ($i);
                // calculate volume data
                $this->updateVolumes ($set, $i, $j);
                // check goals
                if ($goals != null)
                {
                    $this->goals_hit = array_merge($this->goals_hit, Exercise_goal::checkGoalCompleteSet($goals, $exercise, $this->log_items[$i][$j]));
                }
            }
            $this->log_exercises[$i]->logex_1rm = $max_estimate_rm;
            // generate INoL values
            $inol_values = Log_control::calculateINOL($this->log_date, $this->user->user_id, $max_estimate_rm, $exercise_name,  $this->log_items[$i]);
            $this->log_exercises[$i]->logex_inol = $inol_values[0];
            $this->log_exercises[$i]->logex_inol_warmup = $inol_values[1];
            // check goals
            if ($goals != null)
            {
                $this->goals_hit = array_merge($this->goals_hit, Exercise_goal::checkGoalCompleteTotals($goals, $exercise, $this->log_exercises[$i]));
            }
        }

        // warning no exercises
        if (count($this->log_data['exercises']) == 0 && $this->user->user_firstlog)
        {
            $this->warnings['no_exercises'] = true;
        }
        //return goals hit
        if (count($this->goals_hit) > 0)
        {
            Session::flash('goals_hit', $this->goals_hit);
        }
        //return your new records :)
        if (count($this->new_prs) > 0)
        {
            Session::flash('new_prs', $this->new_prs);
        }
        //return your new exercises :)
        if (count($this->new_exercises) > 0)
        {
            Session::flash('new_exercises', $this->new_exercises);
        }
        //return warnings
        if (count($this->warnings) > 0)
        {
            Session::flash('warnings', $this->warnings);
        }
    }

    private function resetLogDefaults ()
    {
        $this->log->log_warmup_volume = 0;
        $this->log->log_warmup_reps = 0;
        $this->log->log_warmup_sets = 0;
        $this->log->log_total_volume = 0;
        $this->log->log_total_reps = 0;
        $this->log->log_total_sets = 0;
        $this->log->log_failed_volume = 0;
        $this->log->log_failed_sets = 0;
        $this->log->log_total_time = 0;
        $this->log->log_total_distance = 0;
    }

    private function updatePrs ($old_records, $set, $i, $j)
    {
        $exercise_id = $this->exercises[$i]['id'];
        $set_weight = $this->log_items[$i][$j]->logitem_abs_weight;
        $set_reps = $set['R'];
        $is_time = $this->log_items[$i][$j]->is_time;
        $is_endurance = $this->log_items[$i][$j]->is_endurance;
        $is_distance = $this->log_items[$i][$j]->is_distance;

        // dont log reps over 100
        if ($set_reps > 100 || $set_reps < 1)
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
            ->where('is_distance', $is_distance)
            ->orderBy('log_date', 'desc')
            ->value('pr_1rm');
        $new_1rm = PRs::generateRM ($set_weight, $set_reps);
        $is_est1rm = (($old_1rm < $new_1rm) || ($is_time == 1 && $is_endurance == 0 && $old_1rm > $new_1rm)) ? true : false;

        // prepare the new pr data for insertion
        $new_pr_data = [
            'exercise_id' => $exercise_id,
            'user_id' => $this->user->user_id,
            'log_date' => $this->log_date,
            'pr_value' => $set_weight,
            'pr_reps' => $set_reps,
            'pr_1rm' => $new_1rm,
            'is_est1rm' => $is_est1rm,
            'is_time' => $is_time,
            'is_endurance' => $is_endurance,
            'is_distance' => $is_distance
        ];

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
            ->where('is_distance', $is_distance)
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
                ->where('is_distance', $is_distance)
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
            ->where('is_distance', $is_distance)
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
                ->where('is_distance', $is_distance)
                ->where('is_pr', 0)
                ->orderBy('log_date', 'asc')
                ->get();
            $last_updated_pr = 0;
            foreach ($sets as $set)
            {
                // make sure the PRs are setting correctly
                if ($set->logitem_abs_weight > $last_updated_pr)
                {
                    // update is_pr flag
                    DB::table('log_items')
                        ->where('log_id', $set->log_id)
                        ->update(['is_pr' => 1]);

                    // check if new 1rm has been set
                    $new_1rm = PRs::generateRM ($set->logitem_abs_weight, $set_reps);
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
                        'is_endurance' => $is_endurance,
                        'is_distance' => $is_distance
                    ]);
                    $last_updated_pr = $set->logitem_abs_weight;
                }
            }
        }

        // insert new entry
        Exercise_record::create($new_pr_data);
    }

    private function insertLogItemWeightTime ($set, $i, $j)
    {
        $value = $set['W'] + $set['T'] + $set['D'];
        $this->log_items[$i][$j]->logitem_time = 0;
        $this->log_items[$i][$j]->logitem_weight = 0;
        $this->log_items[$i][$j]->logitem_distance = 0;
        if ($this->log_items[$i][$j]->is_time)
        {
            $this->log_items[$i][$j]->logitem_time = $value;
        }
        elseif ($this->log_items[$i][$j]->is_distance)
        {
            $this->log_items[$i][$j]->logitem_distance = $value;
        }
        else
        {
            $this->log_items[$i][$j]->logitem_weight = $value;
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
                return Format::correct_time($input[0], 'm', 's', 0);
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
        elseif ($type == 'D')
        {
            if ($input[1] == '')
            {
                return $input[0];
            }
            else
            {
                return Format::correct_distance($input[0], $input[1], 'm', 0);
            }
        }
        else
        {
            return $input;
        }
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
            elseif ($this->exercises[$i]['distance'])
            {
                $set['D'][0] = floatval($set['U']);
                $set['D'][1] = '';
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
                if ($set['W'][0] == '')
                {
                    $set['W'][0] = 0;
                }
            }
            $set['W'] = $this->correctUnitsDatabase ($set['W'], 'W');
            $set['T'] = 0;
            $set['D'] = 0;
        }
        elseif (isset($set['T']))
        {
            $set['T'][0] = str_replace(' ', '', $set['T'][0]);
            $set['T'] = $this->correctUnitsDatabase ($set['T'], 'T');
            // if new exercise and no flags set guess time type
            if ($this->exercises[$i]['new'] && $this->is_speed == null)
            {
                if ($set['T'] > 1800)
                {
                    $this->log_items[$i][$j]->is_endurance = true;
                }
            }
            $this->log_items[$i][$j]->is_time = true;
            $set['W'] = 0;
            $set['D'] = 0;
        }
        elseif (isset($set['D']))
        {
            $set['D'][0] = str_replace(' ', '', $set['D'][0]);
            $set['D'] = $this->correctUnitsDatabase ($set['D'], 'D');
            $this->log_items[$i][$j]->is_distance = true;
            $set['W'] = 0;
            $set['T'] = 0;
        } else {
            $set['W'] = 0;
            $set['T'] = 0;
            $set['D'] = 0;
        }
        $set['R'] = (isset($set['R'])) ? intval($set['R']) : 1;
        $set['S'] = (isset($set['S'])) ? intval($set['S']) : 1;
        return $set;
    }

    private function checkSpecialTags ($string, $i, $j)
    {
        if (empty($string))
        {
            return 0;
        }
        $string = strtolower($string);
        $this->is_speed = null;
        // line is warmup
        if ($this->isWarmupTag ($string))
        {
            $this->log_items[$i][$j]->is_warmup = true;
        }
        elseif ($this->isEnduranceTag ($string))
        {
            $this->log_items[$i][$j]->is_endurance = true;
            $this->is_speed = false;
            $this->log_items[$i][$j]->is_time = true;
        }
        elseif ($this->isDistanceTag ($string))
        {
            $this->log_items[$i][$j]->is_distance = true;
        }
        elseif ($this->isSpeedTag ($string))
        {
            $this->log_items[$i][$j]->is_endurance = false;
            $this->is_speed = true;
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
                    elseif ($this->isDistanceTag ($part))
                    {
                        $this->log_items[$i][$j]->is_distance = true;
                    }
                    elseif ($this->isSpeedTag ($part))
                    {
                        $this->log_items[$i][$j]->is_endurance = false;
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

    private function isSpeedTag ($part)
    {
        if ($part == 's' || $part == 'speed')
        {
            return true;
        }
        return false;
    }

    private function isDistanceTag ($part)
    {
        if ($part == 'd' || $part == 'distance')
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
        elseif ($this->log_items[$i][$j]->is_distance)
        {
            $this->log_items[$i][$j]->logitem_abs_weight = floatval($set['D']);
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
            $this->log_exercises[$i]->logex_time += $this->log_items[$i][$j]->logitem_abs_weight * $set['R'] * $set['S'];
            $this->log->log_total_time += $this->log_items[$i][$j]->logitem_abs_weight * $set['R'] * $set['S'];
        }
        elseif ($this->log_items[$i][$j]->is_distance)
        {
            $this->log_exercises[$i]->logex_distance += $this->log_items[$i][$j]->logitem_abs_weight * $set['R'] * $set['S'];
            $this->log->log_total_distance += $this->log_items[$i][$j]->logitem_abs_weight * $set['R'] * $set['S'];
        }
        else
        {
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
        elseif ($this->log_items[$i][$j]->is_distance)
        {
            // distance
            if (!isset($prs['D'][$set['R']]) ||
                floatval($prs['D'][$set['R']]) < $this->log_items[$i][$j]->logitem_abs_weight)
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

    private function setExerciseDefaultTypes ($i)
    {
        // if new exercise set if it a time based exercise
        if ($this->exercises[$i]['update'])
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
            if ($this->exercises[$i]['distance'])
            {
                $update_exercises['is_distance'] = 1;
            }
            if (count($update_exercises) > 0)
            {
                DB::table('exercises')->where('exercise_id', $this->exercises[$i]['id'])->update($update_exercises);
            }
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
}