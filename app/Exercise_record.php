<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Exercise_record extends Model
{
    protected $primaryKey = 'pr_id';
    protected $dates = ['log_date'];
    protected $dateFormat = 'Y-m-d';
    protected $casts = [
        'is_time' => 'boolean',
        'is_endurance' => 'boolean',
        'is_distance' => 'boolean'
    ];
    protected $guarded = ['pr_id'];

    public function scopeGetexerciseprs($query, $user_id, $log_date, $exercise_name, $exercise_object = false, $return_date = false)
    {
        $query = $query->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                ->select(DB::raw('MAX(pr_value) as pr_value'), 'pr_reps', 'exercise_records.is_time', 'exercise_records.is_endurance', 'exercise_records.is_distance')
                ->where('exercise_records.user_id', $user_id)
                ->where('exercises.exercise_name', $exercise_name)
                ->where('log_date', '<=', $log_date);
        if ($exercise_object !== false)
        {
            $query = $query->where('exercise_records.is_time', $exercise_object->is_time)
                            ->where('exercise_records.is_endurance', $exercise_object->is_endurance)
                            ->where('exercise_records.is_distance', $exercise_object->is_distance);
        }
        if ($return_date)
        {
            $query = $query->addSelect(DB::raw('MAX(log_date) as log_date'));
        }
        $query = $query->groupBy('pr_reps');
        return $query;
    }

    public function scopeGetexerciseprsall($query, $user_id, $range, $exercise_name, $exercise_object = false, $get_bodyweight = true, $show_reps = [1,2,3,4,5,6,7,8,9,10])
    {
        $query = $query->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id');
        if ($get_bodyweight)
        {
            $query = $query->join('logs', function ($join) {
                $join->on('exercise_records.log_date', '=', 'logs.log_date')
                ->on('exercise_records.user_id', '=', 'logs.user_id');
            });
        }
        $query = $query->select('pr_reps', DB::raw('MAX(pr_value) as pr_value'), 'exercise_records.log_date')
                ->where('exercise_records.user_id', $user_id)
                ->where('exercises.exercise_name', $exercise_name)
                ->whereIn('pr_reps', $show_reps);
        if ($exercise_object !== false)
        {
            $query = $query->where('exercise_records.is_time', $exercise_object->is_time)
                            ->where('exercise_records.is_endurance', $exercise_object->is_endurance)
                            ->where('exercise_records.is_distance', $exercise_object->is_distance);
        }
        if ($get_bodyweight)
        {
            $query = $query->addSelect('logs.log_weight');
        }
        if ($range > 0)
        {
            $query = $query->where('log_date', '>=', Carbon::now()->subMonths($range)->toDateString());
        }
        $query = $query->groupBy('pr_reps')
                ->groupBy('log_date')
                ->orderBy('pr_reps', 'asc')
                ->orderBy('log_date', 'asc');
        return $query;
    }

    public function scopeGetlastest1rm($query, $user_id, $exercise_name)
    {
        $query = $query->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                ->select('pr_1rm')
                ->where('exercise_records.user_id', $user_id)
                ->where('exercises.exercise_name', $exercise_name)
                ->where('exercises.is_time', false)
				->where('exercises.is_distance', false)
                ->where('is_est1rm', 1)
                ->orderBy('pr_1rm', 'desc');
        return $query;
    }

    public function scopeGetest1rmall($query, $user_id, $range, $exercise_name, $show_reps = [1,2,3,4,5,6,7,8,9,10])
    {
        $query = $query->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                ->select(DB::raw('MAX(pr_1rm) as pr_value'), 'log_date')
                ->where('exercise_records.user_id', $user_id)
                ->where('exercises.exercise_name', $exercise_name)
                ->where('exercises.is_time', false)
                ->where('is_est1rm', 1)
                ->whereIn('pr_reps', $show_reps);
        if ($range > 0)
        {
            $query = $query->where('log_date', '>=', Carbon::now()->subMonths($range)->toDateString());
        }
        $query = $query->groupBy('log_date')
                ->orderBy('log_date', 'asc');
        return $query;
    }

    public function scopeGetexercisemaxpr($query, $user_id, $exercise_id, $exercise_is_time, $exercise_is_endurance, $exercise_is_distance)
    {
        $query = $query->where('user_id', $user_id)
                ->where('exercise_id', $exercise_id)
                ->where('is_time', $exercise_is_time)
                ->where('is_endurance', $exercise_is_endurance)
                ->where('is_distance', $exercise_is_distance)
                ->orderBy('pr_value', 'desc');
        return $query;
    }

    public static function exercisemaxpr($user_id, $exercise_id, $exercise_is_time, $exercise_is_endurance, $exercise_is_distance)
    {
        $maxpr = Exercise_record::getexercisemaxpr($user_id, $exercise_id, $exercise_is_time, $exercise_is_endurance, $exercise_is_distance);
        if ($maxpr->get() != null)
        {
            return $maxpr->value('pr_value');
        }
        else
        {
            return 0;
        }
    }

    public static function exercisePrs($user_id, $log_date, $exercise_name)
    {
        $prs = Exercise_record::getexerciseprs($user_id, $log_date, $exercise_name)->get();
        $return_prs = array('W' => [], 'T' => [], 'E' => [], 'D' => []);
        foreach ($prs as $pr)
        {
            $pr_type = ($pr->is_distance == true) ? 'D' :
                        ($pr->is_endurance == true) ? 'E' :
                        ($pr->is_time == true) ? 'T' : 'W';
            $return_prs[$pr_type][$pr->pr_reps] = $pr->pr_value;
        }
        return $return_prs;
    }

    public static function filterPrs($collection)
    {
        $last_pr = 0;
        return $collection->reverse()->map(function ($item, $key) use (&$last_pr) {
            if ($item->pr_value < $last_pr)
            {
                $item->pr_value = $last_pr . '*';
            }
            else
            {
                $item->pr_value = (float)$item->pr_value;
            }
            $last_pr = (float)$item->pr_value;
            return $item;
        })->reverse()->groupBy('pr_reps')->toArray();
    }
}
