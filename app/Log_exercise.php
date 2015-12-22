<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log_exercise extends Model
{
    protected $primaryKey = 'logex_id';
    protected $dates = ['pr_date'];

    /**
     * a log exercise can have many log items
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function log_items()
    {
        $this->hasMany('App\Log_item');
    }

    /**
     * Log_exercise belongs to a single log
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function log()
    {
        $this->belongsTo('App\Log');
    }

    /**
     * Log_exercise belongs to a single exercise
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function exercise()
    {
        $this->belongsTo('App\Exercise');
    }
}
