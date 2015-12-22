<?php

namespace App\Extend;

class Time {
    function format_time($time)
    {
    	if ($time <= 60)
    	{
    		return $time . ' secs';
    	}
    	elseif ($time % 3600 == 0)
    	{
    		return ($time / 3600) . ' hours';
    	}
    	elseif ($time % 60 == 0)
    	{
    		return ($time / 60) . ' mins';
    	}
    	else
    	{
    		$output = '';
    		$hours = floor($time / 3600);
    		$time = $time % 3600;
    		$mins = floor($time / 60);
    		$secs = $time % 60;
    		if ($hours > 0)
    		{
    			return $hours . ':' . sprintf("%02d", $mins) . ':' . sprintf("%02d", $secs);
    		}
    		else
    		{
    			return $mins . ':' . sprintf("%02d", $secs);
    		}
    	}
    }
}
