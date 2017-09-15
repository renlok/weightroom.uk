<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Carbon\Carbon;

class Exercise_group extends Model
{
    protected $primaryKey = 'exgroup_id';
    protected $guarded = ['exgroup_id'];

    public function scopeListnotempty($query)
    {
        return $query->select('exgroup_name', 'exercise_groups.exgroup_id')
                      ->join('exercise_group_relations', 'exercise_groups.exgroup_id', '=', 'exercise_group_relations.exgroup_id')
                      ->where('user_id', Auth::user()->user_id)
                      ->groupBy('exercise_group_relations.exgroup_id')
                      ->orderBy('exgroup_name', 'asc');
    }

    /**
     * Exercise_group belongs to a single user
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'user_id');
    }

    /**
     * Exercise_group can have many log Exercise_group_relations
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function exercise_group_relations()
    {
        return $this->hasMany('App\Exercise_group_relation', 'exgroup_id', 'exgroup_id');
    }
}
