<?php

namespace App\Extend;

use DB;
use App\Exercise_record;

class PRs
{
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
						->select('logitem_id', 'logitem_abs_weight', 'logitem_reps', 'log_date', 'is_time', 'is_endurance', 'is_distance')
						->where('exercise_id', $exercise->exercise_id)
						->where('logitem_reps', '<=', 100)
						->where('logitem_reps', '>', 0)
						->orderBy('log_date', 'asc')
						->get();
			$pr = [];
			for ($x = 1; $x <= 100; $x++)
			{
				$pr[$x] = 0;
			}
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
					if (($est1rm < $new1rm && !$item->is_time) || ($est1rm > $new1rm && $item->is_time))
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
						'is_endurance' => $item->is_endurance,
						'is_distance' => $item->is_distance
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
			->select('logitem_id', 'logitem_abs_weight', 'logitem_1rm', 'logitem_reps', 'log_date', 'user_id', 'is_time', 'is_distance', 'is_endurance')
			->where('exercise_id', $exercise_id)
			->where('logitem_reps', '<=', 100)
			->where('logitem_reps', '>', 0)
			->orderBy('log_date', 'asc')
			->get();
		$pr_value = [];
		$pr_distance = [];
		$pr_endurance = [];
		$pr_time = [];
		$est_1rm = 0;
		for ($x = 1; $x <= 100; $x++)
		{
			$pr_value[$x] = 0;
			$pr_distance[$x] = 0;
			$pr_endurance[$x] = 0;
			$pr_time[$x] = 0;
		}
		foreach ($log_items as $log_item)
		{
			$is_est1rm = 0;
			if (($pr_value[$log_item->logitem_reps] < $log_item->logitem_abs_weight && !$log_item->is_time && !$log_item->is_distance && !$log_item->is_endurance)
			 || ($pr_distance[$log_item->logitem_reps] < $log_item->logitem_abs_weight && $log_item->is_distance)
			 || ($pr_endurance[$log_item->logitem_reps] < $log_item->logitem_abs_weight && $log_item->is_endurance)
			 || ($pr_time[$log_item->logitem_reps] > $log_item->logitem_abs_weight && $log_item->is_time))
			{
				if ($log_item->is_time)
				{
					$pr_time[$log_item->logitem_reps] = $log_item->logitem_abs_weight;
				}
				elseif ($log_item->is_distance)
				{
					$pr_distance[$log_item->logitem_reps] = $log_item->logitem_abs_weight;
				}
				elseif ($log_item->is_endurance)
				{
					$pr_endurance[$log_item->logitem_reps] = $log_item->logitem_abs_weight;
				}
				else
				{
					$pr_value[$log_item->logitem_reps] = $log_item->logitem_abs_weight;
					if ($est_1rm < $log_item->logitem_1rm)
					{
						$est_1rm = $log_item->logitem_1rm;
						$is_est1rm = 1;
					}
				}
				DB::table('log_items')
					->where('logitem_id', $log_item->logitem_id)
					->update(['is_pr' => 1]);
				Exercise_record::create([
					'exercise_id' => $exercise_id,
					'user_id' => $log_item->user_id,
					'log_date' => $log_item->log_date,
					'pr_value' => $log_item->logitem_abs_weight,
					'pr_1rm' => $log_item->logitem_1rm,
					'is_est1rm' => $is_est1rm,
					'pr_reps' => $log_item->logitem_reps,
					'is_time' => $log_item->is_time,
					'is_endurance' => $log_item->is_endurance,
					'is_distance' => $log_item->is_distance
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
						if ($item['is_time'])
						{
							return 'T';
						}
						elseif ($item['is_endurance'])
						{
							return 'E';
						}
						elseif ($item['is_distance'])
						{
							return 'D';
						}
						else
						{
							return 'W';
						}
					})
					->groupBy('pr_reps');
		if ($return_date)
		{
			$records = $records->addSelect(DB::raw('MAX(log_date) as log_date'));
		}
		$records = $records->get();
		$prs = ['W' => [], 'T' => [], 'D' => [], 'E' => []];
		$date = ['W' => [], 'T' => [], 'D' => [], 'E' => []];
		while ($row = $db->fetch())
		{
			if ($item['is_time'])
			{
				$type = 'T';
			}
			elseif ($item['is_endurance'])
			{
				$type = 'E';
			}
			elseif ($item['is_distance'])
			{
				$type = 'D';
			}
			else
			{
				$type = 'W';
			}
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

	public static function generateRM ($weight, $reps, $rm = 1)
	{
		if ($reps == $rm)
		{
			return $weight;
		}
		//for all reps > 1 calculate the 1RMs
		$lomonerm = $weight * pow($reps, 1 / 10);
		$brzonerm = $weight * (36 / (37 - $reps));
		$eplonerm = $weight * (1 + ($reps / 30));
		$mayonerm = ($weight * 100) / (52.2 + (41.9 * exp(-1 * ($reps * 0.055))));
		$ocoonerm = $weight * (1 + $reps * 0.025);
		$watonerm = ($weight * 100) / (48.8 + (53.8 * exp(-1 * ($reps * 0.075))));
		$lanonerm = $weight * 100 / (101.3 - 2.67123 * $reps);
		if ($rm == 1)
		{
			// get the average
			return ($lomonerm + $brzonerm + $eplonerm + $mayonerm + $ocoonerm + $watonerm + $lanonerm) / 7;
		}
		$lomrm = floor($lomonerm / (pow($rm, 1 / 10)));
		$brzrm = floor(($brzonerm * (37 - $rm)) / 36);
		$eplrm = floor($eplonerm / ((1 + ($rm / 30))));
		$mayrm = floor(($mayonerm * (52.2 + (41.9 * exp(-1 * ($rm * 0.055))))) / 100);
		$ocorm = floor(($ocoonerm / (1 + $rm * 0.025)));
		$watrm = floor(($watonerm * (48.8 + (53.8 * exp(-1 * ($rm * 0.075))))) / 100);
		$lanrm = floor((($lanonerm * (101.3 - 2.67123 * $rm)) / 100));
		// return the average value
		return floor(($lomrm + $brzrm + $eplrm + $mayrm + $ocorm + $watrm + $lanrm) / 7);
	}
}
