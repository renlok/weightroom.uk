<?php

namespace App\Extend;

use Auth;
use DB;
use App\Exercise;
use App\Exercise_record;

class PRs {
    //TODO rewirte all of this
    public function fix_prs()
	{
		global $db;
		//prepare everything
		$query = "TRUNCATE exercise_records";
		$db->direct_query($query);
		$query = "UPDATE log_items SET is_pr = 0";
		$db->direct_query($query);
		// load the exercises
		$query = "SELECT exercise_id, user_id FROM exercises ORDER BY exercise_id ASC";
		$db->direct_query($query);
		$data = $db->fetchall();
		foreach($data as $row)
		{
			$query = "SELECT logitem_id, logitem_weight, logitem_reps, log_date FROM log_items WHERE exercise_id = :exercise_id ORDER BY log_date ASC";
			$params = array(
				array(':exercise_id', $row['exercise_id'], 'int')
			);
			$db->query($query, $params);
			$pr = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
			while($ex_data = $db->fetch())
			{
				if ($ex_data['logitem_reps'] <= 10 && $ex_data['logitem_reps'] > 0 && $pr[$ex_data['logitem_reps']] < $ex_data['logitem_weight'])
				{
					$pr[$ex_data['logitem_reps']] = $ex_data['logitem_weight'];
					$query = "UPDATE log_items SET is_pr = 1 WHERE logitem_id = :logitem_id";
					$params = array(
						array(':logitem_id', $ex_data['logitem_id'], 'int')
					);
					$db->query($query, $params);
					$query = "INSERT INTO exercise_records (exercise_id, user_id, log_date, pr_value, pr_reps)
							VALUES (:exercise_id, :user_id, :log_date, :pr_value, :pr_reps)";
					$params = array(
						array(':exercise_id', $row['exercise_id'], 'int'),
						array(':user_id', $row['user_id'], 'int'),
						array(':log_date', $ex_data['log_date'], 'str'),
						array(':pr_value', $ex_data['logitem_weight'], 'float'),
						array(':pr_reps', $ex_data['logitem_reps'], 'int')
					);
					$db->query($query, $params);
				}
			}
		}
	}

	public function rebuildExercisePRs($exercise_id)
	{
		// delete existing records
        DB::table('exercise_records')
            ->where('exercise_id', $exercise_id)
            ->delete();
        DB::table('log_items')
            ->where('exercise_id', $exercise_id)
            ->update(['is_pr' => 0]);

        $log_items = DB::table('log_items')
            ->select('logitem_id', 'logitem_abs_weight', 'logitem_reps', 'log_date', 'user_id', 'is_time')
            ->where('exercise_id', $exercise_id)
            ->orderBy('log_date', 'asc')
            ->get();
		$pr_time = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
        $pr_value = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
		foreach ($log_items as $log_item)
		{
			if ($log_item['logitem_reps'] <= 10 && $log_item['logitem_reps'] > 0
             && (($pr_time[$log_item['logitem_reps']] < $log_item['logitem_abs_weight'] && $log_item['is_time'] == 1)
             || ($pr_value[$log_item['logitem_reps']] < $log_item['logitem_abs_weight'] && $log_item['is_time'] == 0)))
			{
                if ($log_item['is_time'])
                {
                    $pr_time[$log_item['logitem_reps']] = $log_item['logitem_abs_weight'];
                } else {
                    $pr_value[$log_item['logitem_reps']] = $log_item['logitem_abs_weight'];
                }
                DB::table('log_items')
                    ->where('logitem_id', $log_item['logitem_id'])
                    ->update(['is_pr' => 1]);
                DB::table('exercise_records')->insert(
                    ['exercise_id' => $exercise_id,
                    'user_id' => $log_item['user_id'],
                    'log_date' => $log_item['log_date'],
                    'pr_value' => $log_item['logitem_abs_weight'],
                    'pr_reps' => $log_item['logitem_reps'],
                    'is_time' => $log_item['is_time']]);
			}
		}
	}

    public function get_prs ($user_id, $log_date, $exercise_name, $return_date = false)
	{
		// load all preceeding prs
        $records = DB::table('exercise_records')
                    ->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                    ->select('pr_reps', 'exercises.is_time', DB::raw('MAX(pr_value) as pr_value'))
                    ->where('exercise_records.user_id', $user_id)
                    ->where('exercises.exercise_name', $exercise_name)
                    ->where('log_date', '<=', $log_date)
                    ->groupBy(function ($item, $key) {
                        return ($item['is_time']) ? 'T' : 'W';
                    })
                    ->groupBy('pr_reps');
        if ($return_date)
        {
            $records = $records->addSelect(DB::raw('MAX(log_date) as log_date'));
        }
        $records = $records->get();
		$prs = array('W' => array(), 'T' => array());
		$date = array('W' => array(), 'T' => array());
		while ($row = $db->fetch())
		{
			$type = ($row['is_time'] == 1) ? 'T' : 'W';
			if ($return_date)
			{
				$date[$type][$row['pr_reps']] = $row['log_date'];
			}
			$prs[$type][$row['pr_reps']] = $row['pr_value'];
		}
		if ($return_date)
		{
			return array($prs, $date);
		}
		else
		{
			return $prs;
		}
	}

    public static function correct_totals($user_id, $exercise_id, $logex_id)
    {
        $exercise_is_time = Exercise::find($exercise_id)->value('is_time');
        $current_1rm = Exercise_record::getexercisemaxpr($user_id, $this->attributes['exercise_id'], $exercise_is_time);
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
                        ->get();
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

    public static function average_intensity($user_id, $exercise_id, $logex_id)
    {
        $log_exercise = PRs::correct_totals($user_id, $exercise_id, $logex_id);

		if (Auth::user()->user_showintensity == 'p')
		{
			if ($current_1rm > 0 && $log_exercise->logex_reps > 0)
			{
				// the current 1rm has been set
				$average_intensity = (($log_exercise->logex_volume / $log_exercise->logex_reps) / $current_1rm) * 100 . '%';
			}
			else
			{
				// TODO: why do you get this, deal with it properly
				$average_intensity = 0;
			}
		}
		else
		{
			$average_intensity = (($log_exercise->logex_reps > 0) ? ($log_exercise->logex_volume / $log_exercise->logex_reps) : 0) . ' ' . Auth::user()->user_unit;
		}
		return round($average_intensity, 1);
    }
}
