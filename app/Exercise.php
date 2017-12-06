<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use DB;
use Auth;

class Exercise extends Model
{
    protected $primaryKey = 'exercise_id';
    protected $casts = [
        'is_time' => 'boolean',
        'is_endurance' => 'boolean',
        'is_distance' => 'boolean'
    ];
    protected $guarded = ['exercise_id'];

    public function scopeListexercises($query, $count)
    {
        if ($count)
        {
            return $query->join('log_exercises', 'log_exercises.exercise_id', '=', 'exercises.exercise_id')
                        ->select('exercises.exercise_id', 'exercises.exercise_name', 'exercises.exercise_name_clean', DB::raw('COUNT(logex_id) as COUNT'))
                        ->where('exercises.user_id', Auth::user()->user_id)
                        ->groupBy('exercises.exercise_id')
                        ->orderBy('COUNT', 'desc');
        }
        else
        {
            return $query->select('exercise_id', 'exercise_name')
                        ->where('user_id', Auth::user()->user_id)
                        ->orderBy('exercise_name', 'asc');
        }
    }

    public function scopeGetexercise($query, $exercise_name, $user_id)
    {
        return $query->where(function ($query) use ($exercise_name) {
                        $query->where('exercise_name', $exercise_name)
                            ->orWhere('exercise_name_clean', $exercise_name);
                    })
                    ->where('user_id', $user_id);
    }

    public function getExerciseNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function setExerciseNameAttribute($value)
    {
        $this->attributes['exercise_name'] = strtolower($value);
    }

    public function getExerciseNameCleanAttribute($value)
    {
        return ucfirst($value);
    }

    public function setExerciseNameCleanAttribute($value)
    {
        $this->attributes['exercise_name_clean'] = strtolower($value);
    }

    /**
     * a log can many log exercises
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function log_exercises()
    {
        return $this->hasMany('App\Log_exercise', 'exercise_id', 'exercise_id');
    }

    /**
     * an exercise can have many goals
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function exercise_goals()
    {
        return $this->hasMany('App\Exercise_goal', 'exercise_id', 'exercise_id');
    }
}
