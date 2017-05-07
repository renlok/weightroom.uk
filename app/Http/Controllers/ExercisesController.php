<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use Carbon\Carbon;
use DB;
use Validator;

use App\Exercise;
use App\Exercise_goal;
use App\Exercise_group;
use App\Exercise_group_relation;
use App\Exercise_record;
use App\Logs;
use App\Log_exercise;
use App\Log_item;
use App\Extend\PRs;
use App\Extend\Format;

class ExercisesController extends Controller
{
    public function getList()
    {
        $exercises = Exercise::listexercises(true)->paginate(50);
        return view('exercise.list', compact('exercises'));
    }

    public function getEdit($exercise_name)
    {
        $exercise = Exercise::select('is_time', 'is_endurance', 'is_distance', 'exercise_id')->where('exercise_name', $exercise_name)->where('user_id', Auth::user()->user_id)->firstOrFail();
        $current_type = 'weight';
        if ($exercise->is_distance)
        {
            $current_type = 'distance';
        }
        elseif ($exercise->is_endurance)
        {
            $current_type = 'enduracne';
        }
        elseif ($exercise->is_time)
        {
            $current_type = 'time';
        }
        $goals = Exercise_goal::where('exercise_id', $exercise->exercise_id)->get();
        $groups = Exercise_group_relation::with('exercise_group')->where('exercise_id', $exercise->exercise_id)->get();
        return view('exercise.edit', compact('exercise_name', 'current_type', 'goals', 'groups'));
    }

    public function postEditName($exercise_name, Request $request)
    {
        $new_name = $request->input('exercisenew');
        $exercise_old = Exercise::where('exercise_name', $exercise_name)->where('user_id', Auth::user()->user_id)->firstOrFail();
        $exercise_new = Exercise::where('exercise_name', $new_name)->where('user_id', Auth::user()->user_id)->first();
        // new name already exists
        if($exercise_new != null)
        {
            $final_id = $exercise_new->$exercise_id;
            // remove the old exercise and merge it with an exsisting one
            // update the exercise id
            DB::table('log_exercises')
                ->where('exercise_id', $exercise_old->$exercise_id)
                ->update(['exercise_id' => $exercise_new->$exercise_id]);
            // update PRs
            PRs::rebuildExercisePRs($exercise_new->$exercise_id);
            // delete the old PRs
            DB::table('exercise_records')
                ->where('exercise_id', $exercise_old->$exercise_id)
                ->delete();
            // delete the old exercise
            $exercise_old->delete();
        }
        else
        {
            $final_id = $exercise_old->exercise_id;
            // rename the exercise
            $exercise_old->exercise_name = $new_name;
            $exercise_old->save();
        }
        // update the log texts
        DB::table('logs')
            ->join('log_exercises', 'logs.log_id', '=', 'log_exercises.log_id')
            ->where('logs.user_id', Auth::user()->user_id)
            ->where('log_exercises.exercise_id', $final_id)
            ->update(['logs.log_update_text' => 1]);
        return redirect()
            ->route('viewExercise', ['exercise_name' => $new_name])
            ->with(['flash_message' => "$exercise_name shall be now known as $new_name"]);
    }

    public function postEdit($exercise_name, Request $request)
    {
        $new_type = $request->input('exerciseType');
        $exercise = Exercise::select('exercise_id')->where('exercise_name', $exercise_name)->where('user_id', Auth::user()->user_id)->firstOrFail();
        $update = ['is_time' => false, 'is_endurance' => false, 'is_distance' => false];
        if ($new_type == 'time')
        {
            $update['is_time'] = true;
        }
        elseif ($new_type == 'enduracne')
        {
            $update['is_time'] = true;
            $update['is_endurance'] = true;
        }
        elseif ($new_type == 'distance')
        {
            $update['is_distance'] = true;
        }
        // update the log texts
        DB::table('exercises')
            ->where('exercise_id', $exercise->exercise_id)
            ->update($update);
        return redirect()
            ->route('viewExercise', ['exercise_name' => $exercise_name])
            ->with(['flash_message' => "$exercise_name has been updated"]);
    }

    public function history($exercise_name, $from_date = '', $to_date = '')
    {
        $user = Auth::user();
        $exercise = Exercise::where('exercise_name', $exercise_name)
                    ->where('user_id', $user->user_id)->firstOrFail();
        $query = $exercise->log_exercises();
        if (!empty($from_date))
        {
            $query = $query->where('log_date', '>=', $from_date);
        }
        if (!empty($to_date))
        {
            $query = $query->where('log_date', '<=', $to_date);
        }
        $scales = [
            'logex_reps' => 1,
            'logex_sets' => 1,
            'logex_1rm' => 1,
        ];
        // set scales
        if ($exercise->is_time)
        {
            $max_volume = Format::correct_time($query->max('logex_time'), 's', 'h');
            $table_name = 'logex_time';
            $scales['logex_time'] = 1;
            $graph_names = ['logex_time' => 'Total Time'];
        }
        elseif ($exercise->is_distance)
        {
            $max_volume = Format::correct_distance($query->max('logex_distance'), 'm', 'km');
            $table_name = 'logex_distance';
            $scales['logex_distance'] = 1;
            $graph_names = ['logex_distance' => 'Total Distance'];
        }
        else
        {
            $max_volume = Format::correct_weight($query->max('logex_volume'));
            $table_name = 'logex_volume';
            $max_reps = $query->max('logex_reps');
            $max_sets = $query->max('logex_sets');
            $max_rm = Exercise_record::exercisemaxpr($user->user_id, $exercise->exercise_id, false, false, false);
            $scales = [
                'logex_volume' => 1,
                'logex_reps' => floor($max_volume / $max_reps),
                'logex_sets' => floor($max_volume / $max_sets),
                'logex_1rm' => floor($max_volume / $max_rm),
            ];
            $graph_names = [
                'logex_volume' => 'Volume',
                'logex_reps' => 'Total reps',
                'logex_sets' => 'Total sets',
                'logex_1rm' => '1RM',
            ];
        }
        // get log_exercises
        $log_exercises = $query->orderBy('log_date', 'desc')->get();
        $exercise_count = $log_exercises->count();
        return view('exercise.history', compact('exercise_name', 'graph_names', 'log_exercises', 'scales', 'user', 'exercise_count'));
    }

    public function volume($exercise_name)
    {
        // TODO
        return view('exercise.volume');
    }

    public function getViewExercise($exercise_name, $type = 'prs', $range = 0, $force_pr_type = null)
    {
        $exercise = Exercise::getexercise($exercise_name, Auth::user()->user_id)->firstOrFail();
        $query = Exercise_record::getexerciseprs(Auth::user()->user_id, Carbon::now()->toDateString(), $exercise_name, $exercise, true)->get();
        $current_prs = $query->groupBy('pr_reps')->toArray();
        $filtered_prs = Exercise_record::filterPrs($query);
        $reps_to_load = array_merge(Auth::user()->user_showreps, Auth::user()->user_showextrareps);
        if ($type == 'weekly' || $type == 'monthly' || $type == 'daily')
        {
            $prs = Log_item::getexercisemaxes(Auth::user()->user_id, $range, $exercise_name, $exercise, $reps_to_load, $type)->get()->groupBy('logitem_reps');
            $prs['Approx. 1'] = Log_item::getestimatedmaxes(Auth::user()->user_id, $range, $exercise_name, $exercise, $type)->get();
            $approx1rm = Exercise_record::getlastest1rm(Auth::user()->user_id, $exercise_name)->value('pr_1rm');
        }
        else
        {
            $prs = Exercise_record::getexerciseprsall(Auth::user()->user_id, $range, $exercise_name, $exercise, false, $reps_to_load)->get()->groupBy('pr_reps');
            $approx1rm = 0;
            if (!($exercise->is_time || $exercise->is_distance))
            {
                $prs['Approx. 1'] = Exercise_record::getest1rmall(Auth::user()->user_id, $range, $exercise_name)->get();
                // be in format [1 => ['log_weight' => ??, 'log_date' => ??]]
                if ($prs['Approx. 1']->count() > 0)
                {
                    $approx1rm = $prs['Approx. 1']->last()->pr_value;
                }
            }
        }
        $graph_label = 'Weight';
        $format_func = 'correct_weight';
        $show_prilepin = true;
        if ($exercise->is_time)
        {
            $graph_label = 'Time';
            $format_func = 'format_time';
            $show_prilepin = false;
        }
        elseif ($exercise->is_distance)
        {
            $graph_label = 'Distance';
            $format_func = 'format_distance';
            $show_prilepin = false;
        }

        return view('exercise.view', compact('exercise_name', 'current_prs', 'filtered_prs', 'prs', 'range', 'type', 'graph_label', 'format_func', 'show_prilepin', 'approx1rm'));
    }

    public function getViewExercisePRHistory($exercise_name)
    {
        $exercise = Exercise::getexercise($exercise_name, Auth::user()->user_id)->firstOrFail();
        $prs = Exercise_record::getexerciseprsall(Auth::user()->user_id, 0, $exercise_name, $exercise, true, array_merge([1,2,3,4,5,6,7,8,9,10], Auth::user()->user_showextrareps))->get()->groupBy(function ($item, $key) {
            return $item['log_date']->toDateString();
        })->toArray();
        $prs = array_map(function($collection) {
            $temp = [];
            $highest = 0;
            $highest_reps = 0;
            foreach ($collection as $item)
            {
                if (!isset($temp['BW']))
                {
                    $temp['BW'] = $item['log_weight'];
                }
                if ($item['pr_value'] > $highest)
                {
                    $highest = $item['pr_value'];
                    $highest_reps = $item['pr_reps'];
                }
                $temp[$item['pr_reps']] = $item['pr_value'];
            }
            $temp['highest'] = $highest;
            $temp['highest_reps'] = $highest_reps;
            return $temp;
        }, $prs);
        krsort ($prs);
        $format_func = 'correct_weight';
        if ($exercise->is_time)
        {
            $format_func = 'format_time';
        }
        elseif ($exercise->is_distance)
        {
            $format_func = 'format_distance';
        }
        return view('exercise.prhistory', compact('exercise_name', 'exercise', 'prs', 'format_func'));
    }

    public function getCompareForm()
    {
        $exercises = Exercise::listexercises(true)->get();
        $exercise_names = [];
        $reps = 0;
        return view('exercise.compareform', compact('exercises', 'exercise_names', 'reps'));
    }

    public function getCompare($reps = 0, $exercise1 = '', $exercise2 = '', $exercise3 = '', $exercise4 = '', $exercise5 = '')
    {
        $exercises = Exercise::listexercises(true)->get();
        $exercise_names = array_map('strtolower', array_filter([$exercise1, $exercise2, $exercise3, $exercise4, $exercise5]));
        $pr_value = ($reps > 0) ? 'pr_value' : 'pr_1rm';
        $records = DB::table('exercise_records')
            ->join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
            ->select(DB::raw("MAX($pr_value) as pr_value"), 'pr_reps', 'log_date', 'exercise_name')
            ->where('exercise_records.user_id', Auth::user()->user_id)
            ->whereIn('exercise_name', $exercise_names);
        if ($reps > 0)
        {
            $records = $records->where('pr_reps', $reps);
        }
        else
        {
            $records = $records->where('is_est1rm', 1);
        }
        $records = $records->groupBy(DB::raw('log_date, exercise_name'))->orderBy('log_date', 'asc')->get();
        return view('exercise.compare', compact('exercises', 'records', 'exercise1', 'exercise2', 'exercise3', 'exercise4', 'exercise5', 'reps', 'exercise_names'));
    }

    public function postCompare(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reps' => 'required|integer',
            'exercises.0' => 'required',
            'exercises.*' => 'exists:exercises,exercise_name,user_id,'.Auth::user()->user_id
        ]);
        if ($validator->fails()) {
            return redirect('exercise/compare')
                    ->withErrors($validator)
                    ->withInput();
        }
        $exercises = $request->input('exercises');
        $route_data = ['reps' => $request->input('reps'), 'exercise1' => $exercises[0]];
        if (isset($exercises[1]))
        {
            $route_data['exercise2'] = $exercises[1];
        }
        if (isset($exercises[2]))
        {
            $route_data['exercise3'] = $exercises[2];
        }
        if (isset($exercises[3]))
        {
            $route_data['exercise4'] = $exercises[3];
        }
        if (isset($exercises[4]))
        {
            $route_data['exercise5'] = $exercises[4];
        }
        return redirect()
            ->route('compareExercises', $route_data);
    }

    public function getExerciseGroups()
    {
        // load exercise groups
        $groups = Exercise_group::with('exercise_group_relations.exercise')->where('user_id', Auth::user()->user_id)->get();
        return view('exercise.groups', compact('groups'));
    }

    public function postNewGroup(Request $request)
    {
        $group_name = $request->input('newgroup', '');
        if (!empty($group_name))
        {
            $group_exists = Exercise_group::where('user_id', Auth::user()->user_id)->where('exgroup_name', $group_name)->first();
            if ($group_exists != null)
            {
                $return_message = 'Group already exists';
            }
            else
            {
                Exercise_group::insert(['user_id' => Auth::user()->user_id, 'exgroup_name' => $group_name]);
                $return_message = 'Group added';
            }
        }
        else
        {
            $return_message = 'Group name cannot be empty';
        }
        return redirect()
            ->route('exerciseGroups')
            ->with(['flash_message' => $return_message]);
    }

    public function getDeleteGroup($group_id)
    {
        Exercise_group::where('user_id', Auth::user()->user_id)->where('exgroup_id', $group_id)->delete();
        return redirect()
            ->route('exerciseGroups')
            ->with(['flash_message' => 'Group deleted']);
    }

    public function getAddToGroup($group_name, $exercise_name)
    {
        $exercise = Exercise::getexercise($exercise_name, Auth::user()->user_id)->first();
        if ($exercise == null)
        {
            return response('no such exercise', 400);
        }
        $group = Exercise_group::where('user_id', Auth::user()->user_id)->where('exgroup_name', $group_name)->firstOrCreate();
        Exercise_group_relation::insert(['exgroup_id' => $group->exgroup_id, 'exercise_id' => $exercise->exercise_id]);
        return response('added', 201);
    }

    public function getDeleteFromGroup($group_name, $exercise_name)
    {
        $exercise = Exercise::getexercise($exercise_name, Auth::user()->user_id)->first();
        if ($exercise == null)
        {
            return response('no such exercise', 400);
        }
        $group = Exercise_group::where('user_id', Auth::user()->user_id)->where('exgroup_name', $group_name)->firstOrCreate();
        Exercise_group_relation::where('exgroup_id', $group->exgroup_id)->where('exercise_id', $exercise->exercise_id)->delete();
        return response('deleted', 201);
    }
}
