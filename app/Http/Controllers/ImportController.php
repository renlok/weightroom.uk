<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon;
use Excel;

class ImportController extends Controller
{
	public function import(Request $request)
    {
		$map = [
			'FitNotes' => [
				'Date' => 'log_date',
				'Exercise' => 'exercise_name',
				'Category' => '',
				'Weight (kgs)' => 'logitem_weight',
				'Reps' => 'logitem_reps',
				'Distance' => 'logitem_comment',
				'Distance Unit' => 'logitem_comment',
				'Time' => 'logitem_time',
			],
			'SimpleWorkoutLog.strength' => [
				'Date' => 'log_date',
				'Time' => '',
				'Exercise' => 'exercise_name',
				'# of Reps' => 'logitem_reps',
				'Weight' => 'logitem_weight',
				'Comments' => 'logitem_comment',
			],
			'SimpleWorkoutLog.cardio' => [
				'Date' => 'log_date',
				'Time' => '',
				'Exercise' => 'exercise_name',
				'Duration' => 'logitem_time',
				'Distance' => 'logitem_comment',
				'Heart Rate' => 'logitem_comment',
				'Calories' => 'logitem_comment'
			],
			'SimpleWorkoutLog.weight' => [
				'Date' => 'log_date',
				'Time' => '',
				'Weight' => 'log_weight'
			],
			'TheSquatRack' => [
				'Performed At' => 'log_date',
				'Exercise' => 'exercise_name',
				'Exercise#' => 'logex_order',
				'Set#' => 'logitem_order',
				'IsPR' => '',
				'String' => 'logitem_comment',
				'Reps' => 'logitem_reps',
				'Weight' => 'logitem_weight',
				'RPE' => 'logitem_pre',
				'Chains' => 'logitem_comment',
				'Duration' => 'logitem_time',
				'Distance' => 'logitem_comment',
				'Box Height' => 'logitem_comment',
			]
		];
		$colomn_names = [
			'log_date',
			'exercise_name',
			'logex_order',
			'logitem_order',
			'logitem_comment',
			'logitem_reps',
			'logitem_weight',
			'logitem_pre',
			'logitem_time',
		];
		if ($request->hasFile('csvfile')) {
			if ($request->file('csvfile')->isValid()) {
			    // do stuff
				$csvfile = $request->file('csvfile');
				Excel::load($csvfile, function($reader) {
				    $results = $reader->get();
					$headers = $reader->first()->keys()->toArray(); // returns array of headers
					foreach ($results as $row)
					{

					}
				});
				return view('import.matchUpload', compact('colomn_names', 'headers'));
			}
		}
    }

	public function importForm()
	{
		return view('import.upload');
	}
}
