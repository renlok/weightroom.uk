<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $primaryKey = 'log_id';
    protected $dates = ['log_date'];
    protected $dateFormat = 'Y-m-d';
    protected $casts = [
        'log_update_text' => 'boolean',
    ];
    protected $guarded = ['log_id'];
    // set defaults
    protected $attributes = array(
       'log_warmup_volume' => 0,
       'log_warmup_reps' => 0,
       'log_warmup_sets' => 0,
       'log_total_volume' => 0,
       'log_total_reps' => 0,
       'log_total_sets' => 0,
       'log_failed_volume' => 0,
       'log_failed_sets' => 0,
       'log_total_time' => 0,
       'log_total_distance' => 0
    );

    public function scopeGetbodyweight($query, $user_id, $from_date = 0)
    {
        $query = $query->select('log_date', 'log_weight')
                        ->where('user_id', $user_id)
                        ->where('log_weight', '!=', 0);
        if ($from_date != 0)
        {
            $query = $query->where('log_date', '>=', $from_date);
        }
        $query = $query->orderBy('log_date', 'asc');
        return $query;
    }

    public function scopeGetlastbodyweight($query, $user_id, $date)
    {
        return $query->where('user_id', $user_id)
                    ->where('log_weight', '!=', 0)
                    ->where('log_date', '<=', $date)
                    ->orderBy('log_date', 'desc');
    }

    public function scopeGetlog($query, $date, $user)
    {
        return $query->where('log_date', $date)->where('user_id', $user);
    }

    public static function isValid($date, $user)
    {
        return (Log::where('log_date', $date)->where('user_id', $user)->count() > 0);
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date);
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
    }

    /**
     * a log can many log exercises
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function log_exercises()
    {
        return $this->hasMany('App\Log_exercise');
    }

    /**
     * logs belongs to a single user
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get all of the log's comments.
     */
    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }
}
