<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log_exercise extends Model
{
    protected $primaryKey = 'logex_id';
    protected $dates = ['log_date'];
    protected $dateFormat = 'Y-m-d';
    protected $guarded = ['logex_id'];

    /**
     * a log exercise can have many log items
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function log_items()
    {
        return $this->hasMany('App\Log_item', 'logex_id');
    }

    /**
     * Log_exercise belongs to a single log
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function log()
    {
        return $this->belongsTo('App\Log');
    }

    /**
     * Log_exercise belongs to a single exercise
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function exercise()
    {
        return $this->belongsTo('App\Exercise');
    }
}
