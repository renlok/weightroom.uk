<?php

namespace App\Extend;

use Auth;
use Carbon;
use DB;
use App\Exercise;
use App\Exercise_record;
use App\Log;

class Log_control {
    public static function correct_totals($user_id, $exercise_id, $logex_id, $current_1rm)
    {
        $log_exercise = DB::table('log_exercises')
                            ->select('logex_volume', 'logex_reps', 'logex_sets', 'logex_failed_volume', 'logex_failed_sets', 'logex_warmup_volume', 'logex_warmup_reps', 'logex_warmup_sets')
                            ->where('logex_id', $logex_id)
                            ->first();
        if (Auth::user()->user_limitintensity > 0)
		{
            $items = DB::table('log_items')
                        ->select(DB::raw('SUM(logitem_abs_weight*logitem_reps*logitem_sets) as logitem_weight, SUM(logitem_reps*logitem_sets) as logitem_reps, SUM(logitem_sets) as logitem_sets'))
                        ->where('logex_id', $logex_id)
                        ->where('logitem_abs_weight', '<', $current_1rm * (Auth::user()->user_limitintensity/100))
                        ->first();
			$log_exercise->logex_volume -= $items->logitem_weight;
            $log_exercise->logex_reps -= $items->logitem_reps;
            $log_exercise->logex_sets -= $items->logitem_sets;
		}

        if (Auth::user()->user_volumeincfails)
		{
			$log_exercise->logex_volume += $log_exercise->logex_failed_volume;
            $log_exercise->logex_reps += $log_exercise->logex_failed_sets;
            $log_exercise->logex_sets += $log_exercise->logex_failed_sets;
		}

        if (Auth::user()->user_limitintensitywarmup)
		{
			$log_exercise->logex_volume -= $log_exercise->logex_warmup_volume;
            $log_exercise->logex_reps -= $log_exercise->logex_warmup_reps;
            $log_exercise->logex_sets -= $log_exercise->logex_warmup_sets;
		}

        return $log_exercise;
    }

    public static function average_intensity($user_id, $exercise_id, $logex_id, $raw = false)
    {
        $exercise = Exercise::select('is_time', 'is_endurance')->find($exercise_id);
        $current_1rm = Exercise_record::exercisemaxpr($user_id, $exercise_id, $exercise->is_time, $exercise->is_endurance);
        $log_exercise = Log_control::correct_totals($user_id, $exercise_id, $logex_id, $current_1rm);

		if (Auth::user()->user_showintensity == 'p')
		{
			if ($current_1rm > 0 && $log_exercise->logex_reps > 0)
			{
				// the current 1rm has been set
				$average_intensity = round((($log_exercise->logex_volume / $log_exercise->logex_reps) / $current_1rm) * 100) . (($raw) ? '' : '%');
			}
			else
			{
				// TODO: why do you get this, deal with it properly
				$average_intensity = 0;
			}
		}
		else
		{
			$average_intensity = (($log_exercise->logex_reps > 0) ? round(($log_exercise->logex_volume / $log_exercise->logex_reps), 1) : 0) . (($raw) ? '' : ' ' . Auth::user()->user_unit);
		}
		return $average_intensity;
    }

    public static function calculate_moving_average($data, $data_keys, $n = 7)
    {
        $data_cleaned = $data;
        $return_data = [];
        $start = floor($n/2);
        $is_object = (is_object($data_cleaned[$start])) ? true : false;
        $return_data[0] = Log_control::array_sum_assoc(array_slice($data_cleaned, 0, $n), $data_keys, $n);
        $return_data[0]['log_date'] = ($is_object) ? $data_cleaned[$start]->log_date : $data_cleaned[$start]['log_date'];
        for ($i = $start + 1, $j = 1, $count = count($data_cleaned); $i < ($count - $start); $i++, $j++)
        {
            foreach ($data_keys as $key)
            {
                $return_data[$j][$key] = round($return_data[$j - 1][$key]
                                                + (($is_object) ? $data_cleaned[$i + $start]->$key : $data_cleaned[$i + $start][$key])/$n
                                                - (($is_object) ? $data_cleaned[$i - ($start + 1)]->$key : $data_cleaned[$i - ($start + 1)][$key])/$n);
            }
            $return_data[$j]['log_date'] = ($is_object) ? $data_cleaned[$i]->log_date : $data_cleaned[$i]['log_date'];
        }
        return $return_data;
    }

    private static function array_sum_assoc($array, $keys, $n)
    {
        $return_array = [];
        // set each to 0
        foreach ($keys as $key)
        {
            $return_array[$key] = 0;
        }
        // sum each value
        foreach ($array as $value)
        {
            foreach ($keys as $key)
            {
                $return_array[$key] += (is_object($value)) ? $value->$key : $value[$key];
            }
        }
        foreach ($keys as $key)
        {
            $return_array[$key] = round($return_array[$key]/$n);
        }
        return $return_array;
    }

    public static function preload_calender_data($date, $user_id)
    {
        $month = Carbon::createFromFormat('Y-m-d', $date)->format('Y-m');
        $lastmonth = Carbon::createFromFormat('Y-m-d', $date)->subMonth();
        $nextmonth = Carbon::createFromFormat('Y-m-d', $date)->addMonth();
        $log_dates = Log::where('user_id', $user_id)
                        ->whereBetween('log_date', [$lastmonth->startOfMonth()->toDateString(), $nextmonth->endOfMonth()->toDateString()])
                        ->lists('log_date');
        $cal_log_dates = json_encode($log_dates);
        $cal_loaded = json_encode([$lastmonth->format('Y-m'), $month, $nextmonth->format('Y-m')]);
        return ['dates' => $cal_log_dates, 'cals' => $cal_loaded];
    }
}
