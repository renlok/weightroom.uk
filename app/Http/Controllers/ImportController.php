<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon;
use Excel;
use Validator;

class ImportController extends Controller
{
	public function import(Request $request)
	{
		// check its a valid file
		$validator = Validator::make($request->all(), [
			'csvfile' => 'required|mimes:csv,xls,txt'
		]);
		if ($validator->fails()) {
			return redirect('import')
						->withErrors($validator)
						->withInput();
		}
		$map = [
			'FitNotes' => [
				'Date' => 'log_date:YYYY-MM-DD',
				'Exercise' => 'exercise_name',
				'Category' => '',
				'Weight (kgs)' => 'logitem_weight:kg',
				'Reps' => 'logitem_reps',
				'Distance' => 'logitem_distance',
				'Distance Unit' => 'logitem_comment',
				'Time' => 'logitem_time',
			],
			'SimpleWorkoutLog.strength' => [
				'Date' => 'log_date:DD/MM/YYYY',
				'Time' => '',
				'Exercise' => 'exercise_name',
				'# of Reps' => 'logitem_reps',
				'Weight' => 'logitem_weight:kg',
				'Comments' => 'logitem_comment',
			],
			'SimpleWorkoutLog.cardio' => [
				'Date' => 'log_date:DD/MM/YYYY',
				'Time' => '',
				'Exercise' => 'exercise_name',
				'Duration' => 'logitem_time',
				'Distance' => 'logitem_distance',
				'Heart Rate' => 'logitem_comment',
				'Calories' => 'logitem_comment'
			],
			'SimpleWorkoutLog.weight' => [
				'Date' => 'log_date:DD/MM/YYYY',
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
				'Weight' => 'logitem_weight:kg',
				'RPE' => 'logitem_pre',
				'Chains' => 'logitem_comment',
				'Duration' => 'logitem_time',
				'Distance' => 'logitem_comment',
				'Box Height' => 'logitem_comment',
			]
		];
		$colomn_names = [
			'log_date:YYYY-MM-DD' => 'Date (YYYY-MM-DD)',
			'log_date:DD/MM/YYYY' => 'Date (DD/MM/YYYY)',
			'log_date:MM/DD/YYYY' => 'Date (MM/DD/YYYY)',
			'log_date:other' => 'Date (Other format)',
			'log_weight' => 'Bodyweight',
			'exercise_name' => 'Exercise Name',
			'logitem_weight:kg' => 'Weight (KG)',
			'logitem_weight:lb' => 'Weight (LB)',
			'logitem_distance' => 'Distance',
			'logitem_time' => 'Time',
			'logitem_reps' => 'Reps',
			'logitem_sets' => 'Sets',
			'logitem_comment' => 'Comment',
			'logitem_pre' => 'RPE',
			'logex_order' => 'Exercise Order',
			'logitem_order' => 'Set Order',
		];
		if ($request->hasFile('csvfile'))
		{
			if ($request->file('csvfile')->isValid())
			{
				// do stuff
				$csvfile = $request->file('csvfile');
				$reader = Excel::load($csvfile, function($reader){});
				$first_row = $reader->first();
				$file_headers = $reader->first()->keys()->toArray(); // returns array of headers
				$link_array = array_flip($file_headers);
				$map_match = '';
				// run this against $map see if we get a match
				foreach ($map as $map_name => $map_values)
				{
					$map_item_keys = array_keys($map_values);
					if ($map_item_keys === $file_headers)
					{
						// we have a winner
						$link_array = $map_values;
						$map_match = $map_name;
						break;
					}
				}
				return view('import.matchUpload', compact('colomn_names', 'file_headers', 'first_row', 'link_array', 'map_match'));
			}
		}
	}

	public function importForm()
	{
		return view('import.upload');
	}
}
