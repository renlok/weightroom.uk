<?php

namespace App\Extend;

use DB;
use App\Exercise_record;

class PRs {
    public static function rebuildPRsTable ()
	{
		//prepare everything
		Exercise_record::truncate();
		DB::table('log_items')->update(['is_pr' => 0]);
		// load the exercises
		$exercises = DB::table('exercises')
						->select('exercise_id', 'user_id')
						->orderBy('exercise_id', 'asc')
						->get();
		$pr_id_array = [];
		$exercise_records = [];
		foreach ($exercises as $exercise)
		{
			$items = DB::table('log_items')
						->select('logitem_id', 'logitem_abs_weight', 'logitem_reps', 'log_date', 'is_time', 'is_endurance')
						->where('exercise_id', $exercise->exercise_id)
						->where('logitem_reps', '<=', 10)
						->where('logitem_reps', '>', 0)
						->orderBy('log_date', 'asc')
						->get();
			$pr = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
            $est1rm = 0;
			foreach ($items as $item)
			{
				if ($pr[$item->logitem_reps] < $item->logitem_abs_weight)
				{
					$pr[$item->logitem_reps] = $item->logitem_abs_weight;
					$pr_id_array[] = $item->logitem_id;
                    if (!$item->is_time)
                    {
                        $new1rm = Parser::generate_rm($item->logitem_abs_weight, $item->logitem_reps);
                    }
                    else
                    {
                        $new1rm = 0;
                    }
                    $is_est1rm = false;
                    if ($est1rm < $new1rm)
                    {
                        $est1rm = $new1rm;
                        $is_est1rm = true;
                    }
					$exercise_records[] = [
						'exercise_id' => $exercise->exercise_id,
						'user_id' => $exercise->user_id,
						'log_date' => $item->log_date,
						'pr_value' => $item->logitem_abs_weight,
						'pr_reps' => $item->logitem_reps,
                        'pr_1rm' => $new1rm,
                        'is_est1rm' => $is_est1rm,
                        'is_time' => $item->is_time,
                        'is_endurance' => $item->is_endurance
					];
				}
			}
		}
		Exercise_record::insert($exercise_records);
		// update log_item is_pr flags
		DB::table('log_items')
			->whereIn('logitem_id', $pr_id_array)
			->update(['is_pr' => 1]);
	}

	public static function rebuildExercisePRs($exercise_id)
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
			if ($log_item->logitem_reps <= 10 && $log_item->logitem_reps > 0
             && (($pr_time[$log_item->logitem_reps] < $log_item->logitem_abs_weight && $log_item->is_time == 1)
             || ($pr_value[$log_item->logitem_reps] < $log_item->logitem_abs_weight && $log_item->is_time == 0)))
			{
                if ($log_item->is_time)
                {
                    $pr_time[$log_item->logitem_reps] = $log_item->logitem_abs_weight;
                } else {
                    $pr_value[$log_item->logitem_reps] = $log_item->logitem_abs_weight;
                }
                DB::table('log_items')
                    ->where('logitem_id', $log_item->logitem_id)
                    ->update(['is_pr' => 1]);
                Exercise_record::create([
                    'exercise_id' => $exercise_id,
                    'user_id' => $log_item->user_id,
                    'log_date' => $log_item->log_date,
                    'pr_value' => $log_item->logitem_abs_weight,
                    'pr_reps' => $log_item->logitem_reps,
                    'is_time' => $log_item->is_time
                ]);
			}
		}
	}

    public static function get_prs ($user_id, $log_date, $exercise_name, $return_date = false)
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
}
