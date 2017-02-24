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
        return $this->belongsTo('App\User', 'follow_user_id', 'user_id');
    }
}
