<?php

namespace App\Extend;

use Auth;
use DB;
use App\Exercise;
use App\Exercise_record;

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
                        ->select(DB::raw('SUM(logitem_weight*logitem_reps*logitem_sets) as logitem_weight, SUM(logitem_reps*logitem_sets) as logitem_reps, SUM(logitem_sets) as logitem_sets'))
                        ->where('logex_id', $logex_id)
                        ->where('logitem_weight', '<', $current_1rm * (Auth::user()->user_limitintensity/100))
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
        $exercise_is_time = Exercise::find($exercise_id)->value('is_time');
        $current_1rm = Exercise_record::exercisemaxpr($user_id, $exercise_id, $exercise_is_time);
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
}
