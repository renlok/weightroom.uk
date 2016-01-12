<?php

namespace App;

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

    public function scopeGetbodyweight($query, $user_id)
    {
        return $query->select('log_date', 'log_weight')
                    ->where('user_id', $user_id)
                    ->where('log_weight', '!=', 0)
                    ->orderBy('log_date', 'asc');
    }

    public function scopeGetlastbodyweight($query, $user_id, $date)
    {
        return $query->where('user_id', $user_id)
                    ->where('log_weight', '!=', 0)
                    ->where('log_date', $date);
    }

    public function scopeGetlog($query, $date, $user)
    {
        return $query->where('log_date', $date)->where('user_id', $user);
    }

    public static function isvalid($date, $user)
    {
        return (Log::where('log_date', $date)->where('user_id', $user)->count() > 0);
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
        return $this->morphMany('App\Comment', 'commentable')->withTrashed();
    }
}
