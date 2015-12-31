<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_follow extends Model
{
    protected $primaryKey = 'follow_id';
    protected $dates = ['follow_date'];
    protected $guarded = ['follow_id'];

    /**
     * invite codes belongs to a single user
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        $this->belongsTo('App\User');
    }
}
