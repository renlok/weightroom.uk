<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Extend\Log_control;

class Log_exercise extends Model
{
    protected $primaryKey = 'logex_id';
    protected $dates = ['log_date'];
    protected $dateFormat = 'Y-m-d';
    protected $guarded = ['logex_id'];
    protected $appends = ['average_intensity'];

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

    /**
     * logs should have an intensity rating
     *
     * @returns double
     */
    public function getAverageIntensityAttribute()
    {
        return $this->attributes['average_intensity'] = Log_control::average_intensity($this->attributes['user_id'], $this->attributes['exercise_id'], $this->attributes['logex_id']);
    }
}
