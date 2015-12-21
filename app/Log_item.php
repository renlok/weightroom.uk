<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log_item extends Model
{
    protected $primaryKey = 'logitem_id';
    protected $dates = ['logitem_date'];
    protected $casts = [
        'is_bw' => 'boolean',
        'is_time' => 'boolean',
        'is_pr' => 'boolean',
        'is_warmup' => 'boolean',
    ];

    /**
     * a user can many log exercises
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function log_exercise()
    {
        $this->belongsTo('App\Log_exercise');
    }
}
