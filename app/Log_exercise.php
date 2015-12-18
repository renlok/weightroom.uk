<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log_exercise extends Model
{
    protected $primaryKey = 'logex_id';
    protected $dates = ['pr_date'];
}
