<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Extend\Format;
use DB;
use Carbon;

class Log_item extends Model
{
    protected $primaryKey = 'logitem_id';
    protected $dates = ['log_date'];
    protected $dateFormat = 'Y-m-d';
    protected $casts = [
        'is_bw' => 'boolean',
        'is_time' => 'boolean',
        'is_pr' => 'boolean',
        'is_warmup' => 'boolean',
        'is_endurance' => 'boolean',
        'is_distance' => 'boolean'
    ];
    protected $appends = ['display_value', 'show_unit'];
    protected $guarded = ['logitem_id'];
    // set defaults
    protected $attributes = array(
        'logitem_reps' => 1,
        'logitem_sets' => 1,
        'is_pr' => false,
        'is_bw' => false,
        'is_time' => false,
        'is_warmup' => false,
        'is_endurance' => false,
        'is_distance' => false
    );

    public function scopeGetexercisemaxes($query, $user_id, $range, $exercise_name, $exercise_object = false, $show_reps = [1,2,3,4,5,6,7,8,9,10], $group_type = 'weekly')
    {
        if ($group_type == 'weekly')
        {
            $group_function = 'WEEK';
        }
        elseif ($group_type == 'monthly')
        {
            $group_function = 'MONTH';
        }
        else
        {
            $group_function = 'DAYOFYEAR';
        }
        $query = $query->select('logitem_abs_weight as pr_value', 'logitem_reps', 'log_date')
                    ->whereIn(DB::raw('(logitem_abs_weight, logitem_reps, ' . $group_function . '(log_date), YEAR(log_date))'), function($query) use ($user_id, $range, $exercise_name, $exercise_object, $show_reps, $group_function) {
                        $query->select(DB::raw('MAX(logitem_abs_weight) as logitem_abs_weight, logitem_reps, ' . $group_function . '(log_date), YEAR(log_date)'))
                                ->from('log_items')
                                ->join('exercises', 'exercises.exercise_id', '=', 'log_items.exercise_id')
                                ->where('log_items.user_id', $user_id)
                                ->where('exercises.exercise_name', $exercise_name)
                                ->whereIn('log_items.logitem_reps', $show_reps);
                        if ($exercise_object !== false)
                        {
                            $query = $query->where('log_items.is_time', $exercise_object->is_time)
                                            ->where('log_items.is_endurance', $exercise_object->is_endurance)
                                            ->where('log_items.is_distance', $exercise_object->is_distance);
                        }
                        if ($range > 0)
                        {
                            $query = $query->where('log_date', '>=', Carbon::now()->subMonths($range)->toDateString());
                        }
                        $query = $query->groupBy(DB::raw('logitem_reps, YEAR(log_date), ' . $group_function . '(log_date)'));
                    });
        if ($range > 0)
        {
            $query = $query->where('log_date', '>=', Carbon::now()->subMonths($range)->toDateString());
        }
        $query = $query->groupBy(DB::raw('logitem_reps, log_date'))->orderBy('log_date');
        return $query;
    }

    public function scopeGetestimatedmaxes($query, $user_id, $range, $exercise_name, $exercise_object = false, $group_type = 'weekly')
    {
        if ($group_type == 'weekly')
        {
            $group_function = 'WEEK';
        }
        elseif ($group_type == 'monthly')
        {
            $group_function = 'MONTH';
        }
        else
        {
            $group_function = 'DAYOFYEAR';
        }
        $query = $query->select('logitem_1rm as pr_value', 'log_date')
                    ->whereIn(DB::raw('(logitem_1rm, ' . $group_function . '(log_date), YEAR(log_date))'), function($query) use ($user_id, $range, $exercise_name, $exercise_object, $group_function) {
                        $query->select(DB::raw('MAX(logitem_1rm) as logitem_1rm, ' . $group_function . '(log_date), YEAR(log_date)'))
                                ->from('log_items')
                                ->join('exercises', 'exercises.exercise_id', '=', 'log_items.exercise_id')
                                ->where('log_items.user_id', $user_id)
                                ->where('exercises.exercise_name', $exercise_name);
                        if ($exercise_object !== false)
                        {
                            $query = $query->where('log_items.is_time', $exercise_object->is_time)
                                            ->where('log_items.is_endurance', $exercise_object->is_endurance)
                                            ->where('log_items.is_distance', $exercise_object->is_distance);
                        }
                        if ($range > 0)
                        {
                            $query = $query->where('log_date', '>=', Carbon::now()->subMonths($range)->toDateString());
                        }
                        $query = $query->groupBy(DB::raw('YEAR(log_date), ' . $group_function . '(log_date)'));
                    });
        if ($range > 0)
        {
            $query = $query->where('log_date', '>=', Carbon::now()->subMonths($range)->toDateString());
        }
        $query = $query->groupBy(DB::raw('logitem_reps, log_date'))->orderBy('log_date');
        return $query;
    }

    /**
     * Log_item belongs to a single Log_exercise
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function log_exercise()
    {
        return $this->belongsTo('App\Log_exercise', 'logex_id');
    }

    /**
     * get display value
     *
     * @returns string
     */
    public function getDisplayValueAttribute()
    {
        if (isset($this->attributes['is_time']) && isset($this->attributes['is_bw']) && isset($this->attributes['is_distance']) && isset($this->attributes['logitem_weight']) && isset($this->attributes['logitem_time']))
        {
            if ($this->attributes['is_time'])
            {
                return Format::format_time($this->attributes['logitem_time']);
            }
            elseif ($this->attributes['is_distance'])
            {
                return Format::format_distance($this->attributes['logitem_distance']);
            }
            elseif ($this->attributes['is_bw'] == 0)
            {
                return Format::correct_weight($this->attributes['logitem_weight']);
            }
            else
            {
                if ($this->attributes['logitem_weight'] != 0)
                {
                    if ($this->attributes['logitem_weight'] < 0)
                    {
                        return 'BW - ' . Format::correct_weight(abs($this->attributes['logitem_weight']));
                    }
                    else
                    {
                        return 'BW + ' . Format::correct_weight($this->attributes['logitem_weight']);
                    }
                }
                else
                {
                    return 'BW';
                }
            }
        }
    }

    /**
     * get display value
     *
     * @returns bool
     */
    public function getShowUnitAttribute()
    {
        if (isset($this->attributes['is_time']) && isset($this->attributes['is_bw']) && isset($this->attributes['logitem_weight']))
        {
            if ($this->attributes['is_time'])
            {
                return false;
            }
            elseif ($this->attributes['is_distance'])
            {
                return false;
            }
            elseif (!$this->attributes['is_bw'])
            {
                return true;
            }
            else
            {
                if ($this->attributes['logitem_weight'] != 0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
    }
}
