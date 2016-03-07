<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Exercise_goal extends Model
{
    protected $primaryKey = 'goal_id';
    protected $guarded = ['goal_id'];

    /**
     * a goal belongs to a single exercise
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function exercise()
    {
        return $this->belongsTo('App\Exercise', 'exercise_id');
    }
}
