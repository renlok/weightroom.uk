<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $primaryKey = 'log_id';
    protected $dates = ['log_date'];
    protected $casts = [
        'log_update_text' => 'boolean',
    ];
    protected $appends = ['average_intensity'];

    public function scopeGetbodyweight($query, $user_id)
    {
        return $query->select('log_date', 'log_weight')
                    ->where('user_id', $user_id)
                    ->where('log_weight', '!=', 0)
                    ->orderBy('log_date', 'asc');
    }

    public function scopeGetlog($query, $date, $user)
    {
        return $query->where('log_date', $date)->where('user_id', $user)->first();
    }

    /**
     * a log can many log exercises
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function log_exercises()
    {
        $this->hasMany('App\Log_exercise');
    }

    /**
     * logs belongs to a single user
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        $this->belongsTo('App\User');
    }

    /**
     * logs should have an intensity rating
     *
     * @returns double
     */
    public function getAverageIntensityAttribute()
    {
        // TODO: make this
    }
}
