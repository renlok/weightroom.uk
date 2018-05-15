<?php

namespace App\Extend;

use Auth;
use App\User;
use App\Notification;

class Format
{
    public static function format_time($time, $span = false)
    {
        if ($time <= 60)
        {
            $value = $time;
            $unit = ' secs';
        }
        elseif ($time % 3600 == 0)
        {
            $value = ($time / 3600);
            $unit = ' hours';
        }
        elseif ($time % 60 == 0)
        {
            $value = ($time / 60);
            $unit = ' mins';
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
                $value = $hours . ':' . sprintf("%02d", $mins) . ':' . sprintf("%02d", $secs);
            }
            else
            {
                $value = $mins . ':' . sprintf("%02d", $secs);
            }
            $unit = '';
        }
        if ($span)
        {
            return '<span class="heavy">' . $value . '</span>' . $unit;
        }
        else
        {
            return $value . $unit;
        }
    }

    public static function format_distance($distance, $span = false)
    {
        if ($distance < 1000)
        {
            $value = $distance;
            $unit = ' m';
        }
        else
        {
            $value = $distance/1000;
            $unit = ' km';
        }
        if ($span)
        {
            return '<span class="heavy">' . $value . '</span>' . $unit;
        }
        else
        {
            return $value . $unit;
        }
    }

    public static function format_weight($weight, $units = 'kg', $span = false)
    {
        $value = Format::correct_weight($weight, $units);
        $unit = ' ' . (Auth::check() ? Auth::user()->user_unit : 'kg');
        if ($span)
        {
            return '<span class="heavy">' . $value . '</span>' . $unit;
        }
        else
        {
            return $value . $unit;
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
        $unit_want = (is_int($unit_want) && $unit_want == 0) ? (Auth::check() ? Auth::user()->user_unit : 'kg') : $unit_want;
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

    public static function addUserLinks($comment, $location, $from = ['url_params' => []])
    {
        // find instances
        if (preg_match_all(
            "/@([^\\#?\s&\/]+):?([0-9]{4}-[0-9]{2}-[0-9]{2})?/im",
            $comment,
            $matches
        ) > 0) {
            // send notifications
            foreach ($matches[1] as $i => $user) {
                if ($user_id = User::where('user_name', preg_replace("/:([0-9]{4}-[0-9]{2}-[0-9]{2})?/im", '', $user))->value('user_id')) {

                    // check if this has been sent before
                    if (Notification::where('user_id', $user_id)
                        ->where('notification_type', 'mention')
                        ->where('notification_from', json_encode(['location' => $location, 'url_params' => $from['url_params']]))
                                ->where('notification_value', Auth::user()->user_name)->first() == null){
                        Notification::create([
                            'user_id' => $user_id,
                            'notification_type' => 'mention',
                            'notification_from' => ['location' => $location, 'url_params' => $from['url_params']],
                            'notification_value' => Auth::user()->user_name
                        ]);
                    }
                }
            }
            $comment = preg_replace(
                ["/@([^\\#?\s&\/]+):([0-9]{4}-[0-9]{2}-[0-9]{2})/im",
                    "/@([^\\#?\s&\/]+)/im"],
                ['<a href="' . url('/log/$2/view/$1') . '">#@#$1:$2</a>',
                    '<a href="' . url('/log/$1') . '">#@#$1</a>'],
                $comment
            );
            $comment = str_replace('">#@#', '">@', $comment);
        }
        return $comment;
    }

    public static function urlSafeString($string)
    {
        return str_replace(['/', '#', '\\', '?', '&'], '-', $string);
    }
}
