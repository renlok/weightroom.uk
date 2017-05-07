<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Exercise_group_relation extends Model
{
    protected $primaryKey = 'exgrprel_id';
    protected $guarded = ['exgrprel_id'];

    /**
     * Exercise_group_relation belongs to a single Exercise_group
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function exercise_group()
    {
        return $this->belongsTo('App\Exercise_group', 'exgroup_id');
    }

    /**
     * Exercise_group_relation belongs to a single exercise
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function exercise()
    {
        return $this->belongsTo('App\Exercise', 'exercise_id');
    }
}
