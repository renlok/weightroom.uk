<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Exercise_record extends Model
{
    protected $primaryKey = 'pr_id';
    protected $dates = ['pr_date'];
    protected $dateFormat = 'Y-m-d';
    protected $casts = [
        'is_time' => 'boolean',
    ];
    protected $guarded = ['pr_id'];

    public function scopeGetexerciseprs($query, $user_id, $log_date, $exercise_name, $return_date = false)
    {
        $query = $query->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                ->select('pr_reps', 'exercises.is_time', DB::raw('MAX(pr_value) as pr_value'))
                ->where('exercise_records.user_id', $user_id)
                ->where('exercises.exercise_name', $exercise_name)
                ->where('pr_date', '<=', $log_date)
                ->groupBy('pr_reps');
        if ($query)
        {
            $query = $query->addSelect(DB::raw('MAX(pr_date) as pr_date'));
        }
        return $query;
    }
}
