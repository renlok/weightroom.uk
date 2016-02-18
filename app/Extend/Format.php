<?php

namespace App\Extend;

use Auth;

class Format {
    public static function format_time($time)
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

    public static function format_distance($distance)
    {
    	if ($distance < 1000)
    	{
    		return $distance . ' m';
    	}
    	else
    	{
    		return $distance/1000 . 'km';
    	}
    }

    public static function correct_time($time, $unit_used = 's', $unit_want = 's', $round = 2) // $unit_used = s/m/h $unit_want = s/m/h
    {
    	$unit_used = ($unit_used == 's') ? 1 : (($unit_used == 'm') ? 2 : 3);
    	$unit_want = ($unit_want == 's') ? 1 : (($unit_want == 'm') ? 2 : 3);
    	if ($unit_used > $unit_want)
    	{
    		$value = ($time * pow (60,($unit_used - $unit_want)));
    	}
    	elseif ($unit_used < $unit_want)
    	{
    		$value =  ($time / pow(60,($unit_want - $unit_used)));
    	}
    	else
    	{
    		$value =  $time;
    	}
        if ($round > 0)
        {
            return round($value, $round);
        }
        else
        {
            return $value;
        }
    }

    public static function correct_weight($weight, $unit_used = 'kg', $unit_want = 0, $round = 20) // $unit_used = kg/lb $unit_want = kg/lb
    {
        $unit_want = ($unit_want == 0) ? Auth::user()->user_unit : $unit_want;
    	if ($unit_used == 'kg' && $unit_want == 'lb')
    	{
    		$value = ($weight * 2.20462); // convert to lb
    	}
    	elseif ($unit_used == 'lb' && $unit_want == 'kg')
    	{
    		$value = ($weight * 0.453592); // convert to kg
    	}
    	else
    	{
    		$value = $weight;
    	}
        if ($round > 0)
        {
            return round($value * $round) / $round;
        }
        else
        {
            return $value;
        }
    }

    public static function correct_distance($distance, $unit_used = 'm', $unit_want = 'm', $round = 2) // $unit_used = m/km/mile $unit_want = m/km/mile
    {
        if ($unit_used == 'km')
        {
            $distance = 1000 * $distance;
            $unit_used = 'm';
        }
    	if ($unit_used == 'm' && $unit_want == 'mile')
    	{
    		$value = ($distance / 1609.344); // convert to mile
    	}
    	elseif ($unit_used == 'mile' && ($unit_want == 'm' || $unit_want == 'km'))
    	{
    		$value = ($distance * 1609.344); // convert to m
    	}
    	else
    	{
            if ($unit_want == 'km')
            {
                $distance = $distance / 1000;
            }
    		$value = $distance;
    	}
        if ($round > 0)
        {
            return round($value, $round);
        }
        else
        {
            return $value;
        }
    }

    public static function replace_video_urls($comment)
	{
		return preg_replace(
			"/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/im",
			"<iframe width=\"420\" height=\"315\" src=\"//www.youtube.com/embed/$2\" frameborder=\"0\" allowfullscreen></iframe>",
			$comment
		);
		//$width = '640';
		//$height = '385';
	}
}
