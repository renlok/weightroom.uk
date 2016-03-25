<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
use App\Log_item;
use App\Log_exercise;

class Exercise_goal extends Model
{
    protected $primaryKey = 'goal_id';
    protected $guarded = ['goal_id'];
    protected $appends = ['percentage'];

    /**
     * a goal belongs to a single exercise
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function exercise()
    {
        return $this->belongsTo('App\Exercise', 'exercise_id');
    }

    /**
     * Goals should have a completed percentage
     *
     * @returns int
     */
    public function getPercentageAttribute()
    {
        switch ($this->attributes['goal_type'])
        {
            case 'wr':
                $value = Log_item::where('exercise_id', $this->attributes['exercise_id'])
                                ->where('user_id', $this->attributes['user_id'])
                                ->where('logitem_reps', $this->attributes['goal_value_two'])
                                ->orderBy('logitem_weight', 'desc')
                                ->value('logitem_weight');
                break;
            case 'rm':
                $value = Log_item::where('exercise_id', $this->attributes['exercise_id'])
                                ->where('user_id', $this->attributes['user_id'])
                                ->orderBy('logitem_reps', 'desc')
                                ->value('logitem_reps');
                break;
            case 'tv':
                $value = Log_exercise::where('exercise_id', $this->attributes['exercise_id'])
                                ->where('user_id', $this->attributes['user_id'])
                                ->orderBy('logex_volume', 'desc')
                                ->value('logex_volume');
                break;
            case 'tr':
                $value = Log_exercise::where('exercise_id', $this->attributes['exercise_id'])
                                ->where('user_id', $this->attributes['user_id'])
                                ->orderBy('logex_reps', 'desc')
                                ->value('logex_reps');
                break;
            default:
                $value = 0;
                break;
        }
        if ($this->attributes['goal_value_one'] <= $value)
        {
            return $this->attributes['percentage'] = 100;
        }
        else
        {
            return $this->attributes['percentage'] = ($this->attributes['goal_value_one'] / 100) * $value;
        }
    }
}
