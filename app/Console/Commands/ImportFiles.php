<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Carbon\Carbon;

use App\Extend\Format;

class ImportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importfiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process some of the import queue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		// TODO:calculate INoL and 1RM values
		$to_delete = [];
		$hash = DB::table('import_data')->orderBy('priority', 'desc')->orderBy('import_id', 'desc')->value('hash');
		$log_data = DB::table('import_data')->where('hash', $hash)->orderBy('user_id')->get();
		foreach ($log_data => $log_line)
		{
			if (!isset($data))
			{
				$date_format = str_replace(['YYYY', 'YY', 'MM', 'DD'], ['Y', 'y', 'n', 'j'], $log_line->log_date_format);
				$data = [
					'user_id' => $log_line->user_id,
					'log_date' => ($log_line->log_date_format == 'YYYY-MM-DD') ? $log_line->log_date : Carbon::createFromFormat($date_format, $log_line->log_date),
					'log_weight' => $log_line->log_weight,
					'log_total_volume' => 0,
					'log_total_reps' => 0,
					'log_total_sets' => 0,
					'log_failed_volume' => 0,
					'log_failed_sets' => 0,
					'log_total_time' => 0,
					'log_total_distance' => 0,
					'log_update_text' => 1,
					'log_exercises' => []
				];
			}
			if (!isset($data['log_exercises'][$log_line->exercise_name]))
			{
				$exercise_id = DB::table('exercises')->where('exercise_name', $log_line->exercise_name)->where('user_id', $log_line->user_id)->value('exercise_id');
				$data['log_exercises'][$log_line->exercise_name] = [
					'exercise_id' => $exercise_id,
					'logex_volume' => 0,
					'logex_reps' => 0,
					'logex_sets' => 0,
					'logex_failed_volume' => 0,
					'logex_failed_sets' => 0,
					'logex_inol' => 0,
					'logex_inol_warmup' => 0,
					'logex_time' => 0,
					'logex_distance' => 0,
					'logex_1rm' => 0,
					'logex_order' => $log_line->logex_order,
					'log_items' => []
				];
			}
			$weight = ($log_line->logitem_weight_is_kg) ? $log_line->logitem_weight : Format::correct_weight($log_line->logitem_weight, 'lb', 'kg');
			$data['log_exercises'][$log_line->exercise_name]['log_items'][] = [
				'logitem_weight' => $weight,
				'logitem_abs_weight' => $weight,
				'logitem_distance' => $log_line->logitem_distance,
				'logitem_time' => $log_line->logitem_time,
				'logitem_reps' => $log_line->logitem_reps,
				'logitem_sets' => $log_line->logitem_sets,
				'logitem_comment' => $log_line->logitem_comment,
				'logitem_pre' => $log_line->logitem_pre,
				'logex_order' => $log_line->logex_order,
				'logitem_order' => $log_line->logitem_order,
			];
			// add to totals
			$data['log_total_volume'] += $weight;
			$data['log_exercises'][$log_line->exercise_name]['logex_volume'] += $weight;
			$data['log_total_reps'] += $log_line->logitem_reps;
			$data['log_exercises'][$log_line->exercise_name]['logex_reps'] += $log_line->logitem_reps;
			$data['log_total_sets'] += $log_line->logitem_sets;
			$data['log_exercises'][$log_line->exercise_name]['logex_sets'] += $log_line->logitem_sets;
			$data['log_total_time'] += $log_line->logitem_time;
			$data['log_exercises'][$log_line->exercise_name]['logex_time'] += $log_line->logitem_time;
			$data['log_total_distance'] += $log_line->logitem_distance;
			$data['log_exercises'][$log_line->exercise_name]['logex_distance'] += $log_line->logitem_distance;
			if ($log_line->logitem_reps == 0)
			{
				$data['log_failed_volume'] += $weight;
				$data['log_exercises'][$log_line->exercise_name]['logex_failed_volume'] += $weight;
				$data['log_failed_sets'] += $log_line->logitem_sets;
				$data['log_exercises'][$log_line->exercise_name]['logex_failed_sets'] += $log_line->logitem_sets;
			}
			// add to delete array
			$to_delete[] = $log_line->import_id;
		}
    }
}
