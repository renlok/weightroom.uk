<?php

namespace App\Extend;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Mail\ImportComplete;
use App\User;

class Import extends ParserPostProcessor
{
    public function __construct()
    {
        parent::__construct('', 0, 0, false);
    }

    public function parseImportData ($hash)
    {
        $log_data = DB::table('import_data')->where('hash', $hash->hash)->where('log_date', $hash->log_date)->orderBy('user_id')->orderBy('logex_order')->orderBy('logitem_order')->get();
        $setup = false;
        $exercise_keys = [];
        $key_counter = 0;
        $to_delete = [];
        $user_id = 0;
        foreach ($log_data as $log_line)
        {
            if (!$setup)
            {
                // setup initial data
                $this->log_update_text = 1;
                $this->log_text = '';
                $this->user = User::where('user_id', $log_line->user_id)->first();
                $user_id = $log_line->user_id;
                $this->getUserWeight (0);
                $this->log_data = array('comment' => '', 'exercises' => array());
                $date_format = str_replace(['YYYY', 'YY', 'MM', 'DD'], ['Y', 'y', 'n', 'j'], $log_line->log_date_format);
                if ($log_line->log_date_format == 'YYYY-MM-DD') {
                    $this->log_date = $log_line->log_date;
                } elseif ($log_line->log_date_format == 'other') {
                    $this->log_date = Carbon::parse($log_line->log_date)->toDateString();
                } else {
                    $this->log_date = Carbon::createFromFormat($date_format, $log_line->log_date)->toDateString();
                }
                $this->user_weight = ($log_line->log_weight == '') ? 0 : $log_line->log_weight;
                $setup = true;
            }
            if (!isset($exercise_keys[$log_line->exercise_name]))
            {
                $this->log_data['exercises'][$key_counter] = [
                    'name' => $log_line->exercise_name,
                    'comment' => '',
                    'data' => []
                ];
                $exercise_keys[$log_line->exercise_name] = $key_counter;
                $key_counter++;
            }
            $item_data = [];
            if ($log_line->logitem_weight_is_bw)
            {
                $item_data['W'][] = ['BW', ($log_line->logitem_weight_is_kg) ? 'kg' : 'lb'];
                if ($log_line->logitem_weight != '')
                {
                    if ((float)$log_line->logitem_weight > 0) {
                        $item_data['W'][0] .= ' + ';
                    } elseif ((float)$log_line->logitem_weight < 0) {
                        $item_data['W'][0] .= ' - ';
                    }
                    $item_data['W'][0] .= (float)$log_line->logitem_weight;
                }
            }
            elseif ((float)$log_line->logitem_weight > 0 && $log_line->logitem_weight != '')
            {
                $item_data['W'] = [(float)$log_line->logitem_weight, ($log_line->logitem_weight_is_kg) ? 'kg' : 'lb'];
            }
            elseif ((float)$log_line->logitem_distance > 0 && $log_line->logitem_distance != '')
            {
                $item_data['D'] = [(float)$log_line->logitem_distance, 'km'];
            }
            elseif ((float)$log_line->logitem_time > 0 && $log_line->logitem_time != '')
            {
                $item_data['T'] = [(float)$log_line->logitem_time, 'm'];
            }
            if (!isset($item_data['W']) && !isset($item_data['D']) && !isset($item_data['T']))
            {
                $item_data['W'] = ['BW', ($log_line->logitem_weight_is_kg) ? 'kg' : 'lb'];
            }
            if ((int)$log_line->logitem_reps > 0 && $log_line->logitem_reps != '')
            {
                $item_data['R'] = (int)$log_line->logitem_reps;
            }
            if ((int)$log_line->logitem_sets > 0 && $log_line->logitem_sets != '')
            {
                $item_data['S'] = (int)$log_line->logitem_sets;
            }
            if ((float)$log_line->logitem_pre != '')
            {
                $item_data['P'] = (float)$log_line->logitem_pre;
            }
            if ($log_line->logitem_comment != '')
            {
                $item_data['C'] = $log_line->logitem_comment;
            }
            $this->log_data['exercises'][$exercise_keys[$log_line->exercise_name]]['data'][] = $item_data;
            // add to delete array
            $to_delete[] = $log_line->import_id;
        }
        DB::table('import_data')->whereIn('import_id', $to_delete)->delete();

        // if the users import is complete send them an email
        if (DB::table('import_data')->where('user_id', $user_id)->first() == null)
        {
            Mail::to($this->user)->send(new ImportComplete());
        }
    }
}
