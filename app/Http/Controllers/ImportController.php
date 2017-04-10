<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Auth;
use Carbon;
use DB;
use Excel;
use Validator;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        // can the user import a file yet?
        $has_log_data = DB::table('import_data')->where('user_id', Auth::user()->user_id)->first();
        if ($has_log_data != null)
        {
            return redirect('import')->with('flash_message', 'You still have a file in the queue. Please wait for that to be processed first.');
        }
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
                'Performed At' => 'log_date:other',
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
        $column_names = [
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
                $first_row = $reader->first()->toArray();
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
                // store the file
                $tmpFilePath = '/temp/';
                $tmpFileName = time() . '-' . $csvfile->getClientOriginalName();
                $request->session()->put('csvfile', [
                    public_path() . $tmpFilePath,
                    $tmpFileName,
                    $csvfile->getMimeType()
                ]);
                // clean first row keys
                $tmp = $first_row;
                $first_row = [];
                foreach ($tmp as $key => $value) {
                    $new_key = preg_replace("/[^A-Za-z0-9]/", '_', $key);
                    $first_row[$new_key] = $value;
                    $file_headers[$new_key] = $key;
                }
                $link_array = array_flip($link_array);
                array_walk($link_array, function(&$value, $key) {
                    $value = preg_replace("/[^A-Za-z0-9]/", '_', $value);
                });
                $link_array = array_flip($link_array);
                $request->session()->put('csvfirstline', $first_row);
                $csvfile->move(public_path() . $tmpFilePath, $tmpFileName);
                return view('import.matchUpload', compact('column_names', 'file_headers', 'first_row', 'link_array', 'map_match'));
            }
        }
    }

    public function storeImport(Request $request)
    {
        $csv_file_data = $request->session()->get('csvfile');
        $validator_values = $request->session()->get('csvfirstline');
        $hash = sha1(Auth::user()->user_id . microtime());

        $column_string = '';
        $extra_sql = '';
        foreach ($request->input() as $key => $column)
        {
            if ($key == '_token')
                continue;
            if (!empty($column_string))
            {
                $column_string .= ',';
            }
            switch ($column)
            {
                case 'log_date:YYYY-MM-DD':
                case 'log_date:DD/MM/YYYY':
                case 'log_date:MM/DD/YYYY':
                    $parts = explode(':', $column);
                    $column_string .= $parts[0];
                    // check date format is correct
                    $date_format = str_replace(['YYYY', 'YY', 'MM', 'DD'], ['Y', 'y', 'n', 'j'], $parts[1]);
                    $dateTime = \DateTime::createFromFormat($date_format, $validator_values[$key]);
                    $errors = \DateTime::getLastErrors();
                    if (!empty($errors['warning_count'])) {
                        return back()->withInput()->with('flash_message', 'Date format doesn\'t match');
                    }
                    $extra_sql .= ", log_date_format = '{$parts[1]}'";
                    break;
                case 'log_date:other':
                    // check date format is correct
                    try {
                        $date = new \DateTime($validator_values[$key]);
                    } catch (Exception $e) {
                        return back()->withInput()->with('flash_message', 'Date format doesn\'t match');
                    }
                    $column_string .= 'log_date';
                    $extra_sql .= ", log_date_format = 'other'";
                    break;
                case 'logitem_weight:kg':
                case 'logitem_weight:lb':
                    $column_string .= 'logitem_weight';
                    $is_kg = intval($column == 'logitem_weight:kg');
                    $extra_sql .= ", logitem_weight_is_kg = '$is_kg'";
                    break;
                case 'log_weight':
                case 'logitem_distance':
                case 'logitem_time':
                case 'logitem_reps':
                case 'logitem_sets':
                case 'logitem_pre':
                case 'logex_order':
                case 'logitem_order':
                    if (!is_numeric($validator_values[$key])) {
                        return back()->withInput()->with('flash_message', 'Date format doesn\'t match');
                    }
                case 'exercise_name':
                case 'logitem_comment':
                    $column_string .= $column;
                    break;
                default:
                    $column_string .= '@dummy';
                    break;
            }
        }
        $csvfile = new UploadedFile($csv_file_data[0] . $csv_file_data[1], $csv_file_data[1], $csv_file_data[2]);
        $query = sprintf("LOAD DATA LOCAL INFILE '%s' INTO TABLE import_data
                FIELDS TERMINATED BY ','
                LINES TERMINATED BY '\\n'
                OPTIONALLY ENCLOSED BY '\"'
                IGNORE 1 LINES
                ($column_string)
                SET user_id = %d, hash = '%s' $extra_sql", addslashes($csv_file_data[0] . $csv_file_data[1]), Auth::user()->user_id, $hash);

        \DB::connection()->getpdo()->exec($query);

        // delete the file
        unlink($csv_file_data[0] . $csv_file_data[1]);

        return redirect()->route('successImport');
    }

    public function importSuccess()
    {
        return view('import.success');
    }

    public function importForm()
    {
        return view('import.upload');
    }

    public function export(Request $request)
    {

    }
}
