<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $primaryKey = 'log_id';
    protected $dates = ['log_date'];

    public function scopeGetbodyweight($query, $user_id)
    {
        return $query->select('log_date', 'log_weight')
                    ->where('user_id', $user_id)
                    ->where('log_weight', '!=', 0)
                    ->orderBy('log_date', 'asc');
    }
}
