<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\Log;
use App\Exercise_record;
use App\Extend\Graph;

class ToolsController extends Controller
{
    public function index()
    {
        return view('tools.index');
    }

    public function bodyweight($range = 0)
    {
        $graphs = [];
        $graphs['Bodyweight'] = Log::getbodyweight(Auth::user()->user_id)->unique('log_weight')->get()->toArray();
        return view('tools.bodyweight', compact('range', 'graphs'));
    }

    public function wilks($range = 0)
    {
        $graph_data = Exercise_record::select('log_date', 'exercise_name', 'pr_1rm as log_weight')
                                ->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                                ->where('exercise_records.user_id', Auth::user()->user_id)
                                ->where('is_est1rm', 1)
                                ->whereIn('exercise_records.exercise_id', [Auth::user()->user_squatid, Auth::user()->user_deadliftid, Auth::user()->user_benchid])
                                ->orderBy('log_date', 'asc');
        $graphs = $graph_data->get()->groupBy('exercise_name')->toArray();
        $graphs['Bodyweight'] = Log::getbodyweight(Auth::user()->user_id)->get();
        // build a useful array for wilks data
        $wilks_exercises = $graph_data->get()->groupBy(function ($item, $key) {
            return $item['log_date']->toDateString();
        })->toArray();
        $wilks_bodyweight = $graphs['Bodyweight']->groupBy(function ($item, $key) {
            return $item['log_date']->toDateString();
        })->toArray();
        $graphs['Bodyweight'] = $graphs['Bodyweight']->unique('log_weight')->toArray();
        $wilks_data = array_merge_recursive($wilks_exercises, $wilks_bodyweight);
        // map
        $temp = [];
        $graphs['Wilks'] = array_filter(array_map(function($key, $item) use (&$temp){
            foreach ($item as $exercise)
            {
                if (isset($exercise['exercise_name']))
                {
                    $temp[$exercise['exercise_name']] = $exercise['log_weight'];
                }
                else
                {
                    $temp['bodyweight'] = $exercise['log_weight'];
                }
            }
            if (count($temp) == 4)
            {
                $bw = $temp['bodyweight'];
                unset($temp['bodyweight']);
                $wilks = Graph::calculate_wilks (array_sum($temp), $bw, Auth::user()->user_gender);
                $temp = [];
                return ['log_weight' => $wilks, 'log_date' => $item['log_date']];
            }
        }, array_keys($wilks_data), $wilks_data));
        return view('tools.wilks', compact('range', 'graphs'));
    }

    public function sinclair($range = 0)
    {
        $graph_data = Exercise_record::select('log_date', 'exercise_name', 'pr_1rm as log_weight')
                                ->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                                ->where('exercise_records.user_id', Auth::user()->user_id)
                                ->where('is_est1rm', 1)
                                ->whereIn('log_items.exercise_id', [Auth::user()->user_snatchid, Auth::user()->user_cleanjerkid])
                                ->orderBy('log_date', 'asc');
        $graphs = $graph_data->get()->groupBy('exercise_name')->toArray();
        $graphs['Bodyweight'] = Log::getbodyweight(Auth::user()->user_id)->get();
        // build a useful array for wilks data
        $wilks_exercises = $graph_data->get()->groupBy(function ($item, $key) {
            return $item['log_date']->toDateString();
        })->toArray();
        $wilks_bodyweight = $graphs['Bodyweight']->groupBy(function ($item, $key) {
            return $item['log_date']->toDateString();
        })->toArray();
        $graphs['Bodyweight'] = $graphs['Bodyweight']->unique('log_weight')->toArray();
        $wilks_data = array_merge_recursive($wilks_exercises, $wilks_bodyweight);
        // map
        $temp = [];
        $graphs['Sinclair'] = array_filter(array_map(function($key, $item) use (&$temp){
            foreach ($item as $exercise)
            {
                if (isset($exercise['exercise_name']))
                {
                    $temp[$exercise['exercise_name']] = $exercise['log_weight'];
                }
                else
                {
                    $temp['bodyweight'] = $exercise['log_weight'];
                }
            }
            if (count($temp) == 3)
            {
                $bw = $temp['bodyweight'];
                unset($temp['bodyweight']);
                $wilks = Graph::calculate_sinclair (array_sum($temp), $bw, Auth::user()->user_gender);
                $temp = [];
                return ['log_weight' => $wilks, 'log_date' => $item['log_date']];
            }
        }, array_keys($wilks_data), $wilks_data));
        return view('tools.sinclair', compact('range', 'graphs'));
    }

    public function invites()
    {
        $user = User::find(Auth::user()->user_id);
        $codes = $user->invite_codes->toArray();
        //$codes = Invite_code::valid(Auth::user()->user_id)->get();
        return view('tools.invites', compact('codes'));
    }
}
