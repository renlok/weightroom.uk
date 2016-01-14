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
    ];
    protected $appends = ['display_value', 'show_unit'];
    protected $guarded = ['logitem_id'];

    public function scopeGetexercisemaxes($query, $user_id, $range, $exercise_name, $is_time = false, $show_reps = [1,2,3,4,5,6,7,8,9,10], $group_type = 'weekly')
    {
        $group_function = ($group_type == 'weekly') ? 'WEEK' : 'MONTH';
        return $query->select('logitem_abs_weight as pr_value', 'logitem_reps', 'log_date')
                    ->whereIn(DB::raw('(logitem_abs_weight, logitem_reps, ' . $group_function . '(log_date))'), function($query) use ($user_id, $range, $exercise_name, $is_time, $show_reps, $group_function) {
                        $query->select(DB::raw('MAX(logitem_abs_weight) as logitem_abs_weight, logitem_reps, ' . $group_function . '(log_date)'))
                                ->from('log_items')
                                ->join('exercises', 'exercises.exercise_id', '=', 'log_items.exercise_id')
                                ->where('log_items.user_id', $user_id)
                                ->where('log_items.is_time', $is_time)
                                ->where('exercises.exercise_name', $exercise_name)
                                ->whereIn('log_items.logitem_reps', $show_reps);
                        if ($range > 0)
                        {
                            $query = $query->where('log_date', '>=', Carbon::now()->subMonths($range)->toDateString());
                        }
                        $query = $query->groupBy(DB::raw('logitem_reps, ' . $group_function . '(log_date)'));
                    })
                    ->groupBy(DB::raw('logitem_reps, log_date'));
    }

    /**
     * a user can many log exercises
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
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
        if (isset($this->attributes['is_time']) && isset($this->attributes['is_bw']) && isset($this->attributes['logitem_weight']) && isset($this->attributes['logitem_time']))
        {
            if ($this->attributes['is_time'])
    		{
    			return Format::format_time($this->attributes['logitem_time']);
    		}
    		elseif ($this->attributes['is_bw'] == 0)
    		{
    			return $this->attributes['logitem_weight'];
    		}
    		else
    		{
    			if ($this->attributes['logitem_weight'] != 0)
    			{
    				if ($this->attributes['logitem_weight'] < 0)
    				{
    					return 'BW - ' . abs($this->attributes['logitem_weight']);
    				}
    				else
    				{
    					return 'BW + ' . $this->attributes['logitem_weight'];
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
    		elseif ($this->attributes['is_bw'] == 0)
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
