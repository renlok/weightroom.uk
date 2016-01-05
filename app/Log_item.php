<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Extend\Format;

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
