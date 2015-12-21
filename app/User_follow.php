<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_follow extends Model
{
    protected $primaryKey = 'follow_id';
    protected $dates = ['follow_date'];
}
