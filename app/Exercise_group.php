<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Exercise_group extends Model
{
    protected $primaryKey = 'exgroup_id';
    protected $guarded = ['exgroup_id'];

    /**
     * Exercise_group belongs to a single user
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Exercise_group can have many log Exercise_group_relations
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function exercise_group_relations()
    {
        return $this->hasMany('App\Exercise_group_relation', 'exgroup_id');
    }
}
