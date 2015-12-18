<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log_comment extends Model
{
    protected $primaryKey = 'comment_id';
    protected $dates = ['comment_date', 'log_date'];
}
