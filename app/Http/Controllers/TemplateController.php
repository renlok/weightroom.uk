<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Exercise;
use App\Exercise_record;
use App\Log;
use App\Template;
use App\Template_log;
use App\Extend\PRs;
use App\Extend\Log_control;

use Auth;
use Carbon;
use Validator;

class TemplateController extends Controller
{
    public function home()
    {
        $template_groups = Template::all()->groupBy('template_type');
        return view('templates.index', compact('template_groups'));
    }

    public function viewTemplate($template_id)
    {
        $template = Template::with([
                'template_logs.template_log_exercises' => function($query) {
                    $query->orderBy('template_log_exercises.logtempex_order', 'asc');
                },
                'template_logs.template_log_exercises.template_log_items' => function($query) {
                    $query->orderBy('template_log_items.logtempex_order', 'asc')
                        ->orderBy('template_log_items.logtempitem_order', 'asc');
                }
            ])
            ->where('template_id', $template_id)->firstorfail();
        $template_exercises = [];
        foreach ($template->template_logs as $log)
        {
            foreach ($log->template_log_exercises as $log_exercises)
            {
                if (!in_array($log_exercises->texercise_name, $template_exercises))
                {
                    $template_exercises[] = $log_exercises->texercise_name;
                }
            }
        }
        $exercises = Exercise::listexercises(true)->get();
        return view('templates.view', compact('template', 'template_exercises', 'exercises'));
    }

    public function postBuildTemplate(Request $request)
    {
        // check inputs
        //check log_id is valid
        $log = Template_log::with([
                'template_log_exercises' => function($query) {
                    $query->orderBy('template_log_exercises.logtempex_order', 'asc');
                },
                'template_log_exercises.template_log_items' => function($query) {
                    $query->orderBy('template_log_items.logtempex_order', 'asc')
                        ->orderBy('template_log_items.logtempitem_order', 'asc');
                }
            ])
            ->where('template_log_id', $request->log_id)->where('has_fixed_values', $request->has_fixed_values)->firstOrFail();
        $exercise_values = [];
        $exercise_names = [];
        if (!$request->has_fixed_values)
        {
            foreach ($request->exercise as $key => $exercise)
            {
                if ($exercise == 0 && ($request->weight[$key] == '' || intval($request->weight[$key]) == 0))
                {
                    return redirect()->back()
                            ->withInput()
                            ->with(['flash_message' => 'Please enter weight or select an exercise to generate the workout from', 'flash_message_type' => 'danger', 'flash_message_important' => true]);
                }
                else
                {
                    if ($exercise > 0)
                    {
                        // check exercise exists
                        $exercise_names[$key] = Exercise::select('exercise_name')->where('exercise_id', $exercise)->where('user_id', Auth::user()->user_id)->value('exercise_name');
                        if ($exercise_names[$key] == null)
                        {
                            return redirect()->back()
                                    ->withInput()
                                    ->with(['flash_message' => 'Please select a valid exercise', 'flash_message_type' => 'danger', 'flash_message_important' => true]);
                        }
                    }
                }
            }
        }
        foreach ($log->template_log_exercises as $log_exercises)
        {
            $loaded = [];
            $entered_1rm = ($request->weight[$log_exercises->logtempex_order] != '' && intval($request->weight[$log_exercises->logtempex_order]) > 0) ? true : false;
            foreach ($log_exercises->template_log_items as $log_items)
            {
                if ($log_items->is_bw)
                {
                    $exercise_values[$log_items->logtempitem_id] = 'BW';
                }
                elseif ($log_items->is_weight)
                {
                    $exercise_values[$log_items->logtempitem_id] = $log_items->logtempitem_weight;
                }
                elseif ($log_items->is_time)
                {
                    $exercise_values[$log_items->logtempitem_id] = $log_items->logtempitem_time;
                }
                elseif ($log_items->is_distance)
                {
                    $exercise_values[$log_items->logtempitem_id] = $log_items->logtempitem_distance;
                }
                elseif ($log_items->is_percent_1rm)
                {
                    if (!isset($loaded[1]))
                    {
                        if ($entered_1rm)
                        {
                            $loaded[1] = $request->weight[$log_exercises->logtempex_order];
                        }
                        else
                        {
                            $query = Exercise_record::getlastest1rm(Auth::user()->user_id, $exercise_names[$log_exercises->logtempex_order])->first();
                            $loaded[1] = $query->pr_1rm;
                        }
                    }
                    $exercise_values[$log_items->logtempitem_id] = $loaded[1] * ($log_items->percent_1rm/100);
                }
                elseif ($log_items->is_current_rm)
                {
                    if (!isset($loaded[$log_items->current_rm]))
                    {
                        if ($entered_1rm)
                        {
                            $loaded[$log_items->current_rm] = PRs::generateRM($request->weight[$log_exercises->logtempex_order], 1, $log_items->current_rm);
                        }
                        else
                        {
                            $loaded[$log_items->current_rm] = Exercise_record::join('exercises', 'exercise_records.exercise_id', '=', 'exercises.exercise_id')
                                        ->where('exercise_records.user_id', Auth::user()->user_id)
                                        ->where('exercises.exercise_name', $exercise_names[$log_exercises->logtempex_order])
                                        ->where('pr_reps', $log_items->current_rm)
                                        ->orderBy('pr_value', 'DESC')
                                        ->value('pr_value');
                        }
                    }
                    $exercise_values[$log_items->logtempitem_id] = $loaded[$log_items->current_rm];
                }
                if ($log_items->has_plus_weight)
                {
                    if ($log_items->is_bw)
                    {
                        if ($log_items->logtempitem_plus_weight > 0)
                        {
                            $exercise_values[$log_items->logtempitem_id] .= ' + ' . $log_items->logtempitem_plus_weight;
                        }
                        else
                        {
                            $exercise_values[$log_items->logtempitem_id] .= ' - ' . $log_items->logtempitem_plus_weight;
                        }
                    }
                    else
                    {
                        $exercise_values[$log_items->logtempitem_id] += $log_items->logtempitem_plus_weight;
                    }
                }
            }
        }
        // set up variables for blade
        $template_name = Template::where('template_id', $log->template_id)->value('template_name');
        $calender = Log_control::preload_calender_data(Carbon::now()->toDateString(), Auth::user()->user_id);
        return view('templates.build', compact('template_name', 'log', 'exercise_values', 'exercise_names', 'calender'));
    }

    public function saveTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'log_date' => 'required|date_format:Y-m-d',
            'template_text' => 'required',
        ]);

        if ($validator->fails())
        {
            return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->with('fail', true)
                    ->withInput();
        }

        if (Log::isValid($request->input('log_date'), Auth::user()->user_id))
        {
            $route = 'editLog';
        }
        else
        {
            $route = 'newLog';
        }
        return redirect()
                ->route($route, ['date' => $request->input('log_date')])
                ->with([
                    'template_text' => $request->input('template_text')
                ]);
    }
}
