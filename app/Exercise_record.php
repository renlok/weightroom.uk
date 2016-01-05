<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Exercise_record extends Model
{
    protected $primaryKey = 'pr_id';
    protected $dates = ['log_date'];
    protected $dateFormat = 'Y-m-d';
    protected $casts = [
        'is_time' => 'boolean',
    ];
    protected $guarded = ['pr_id'];

    public function scopeGetexerciseprs($query, $user_id, $log_date, $exercise_name, $is_time = false)
    {
        $query = $query->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                ->where('exercise_records.user_id', $user_id)
                ->where('exercises.exercise_name', $exercise_name)
                ->where('is_time', $is_time)
                ->where('log_date', '<=', $log_date)
                ->groupBy('pr_reps')
                ->lists(DB::raw('MAX(pr_value) as pr_value'), 'pr_reps');
        return $query;
    }

    public function scopeGetexerciseprsall($query, $user_id, $log_date, $exercise_name, $is_time = false)
    {
        $query = $query->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                ->select('pr_reps', DB::raw('MAX(pr_value) as pr_value'), DB::raw('MAX(log_date) as log_date'))
                ->where('exercise_records.user_id', $user_id)
                ->where('exercises.exercise_name', $exercise_name)
                ->where('is_time', $is_time)
                ->where('log_date', '<=', $log_date)
                ->orderBy('log_date', 'desc');
        return $query;
    }
}
