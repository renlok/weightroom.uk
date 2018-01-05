<?php

namespace App\Extend;


class Templates
{
    public static function loadVariableExerciseList($template_logs)
    {
        $template_exercises = [];
        foreach ($template_logs as $log)
        {
            if (!$log->has_fixed_values) {
                foreach ($log->template_log_exercises as $log_exercises) {
                    if (!in_array($log_exercises->texercise_name, $template_exercises)) {
                        $template_exercises[] = $log_exercises->texercise_name;
                    }
                }
            }
        }
        return $template_exercises;
    }

    // $template -> template object
    public static function loadJSONData($template)
    {
        $json_data = [];
        foreach ($template->template_logs as $log)
        {
            $log_data = [
                'log_name' => $log->template_log_name,
                'log_week' => $log->template_log_week,
                'log_day' => $log->template_log_day,
                'exercise_data' => []
            ];
            foreach ($log->template_log_exercises as $log_exercises)
            {
                $exercise_data = [
                    'exercise_name' => $log_exercises->texercise_name,
                    'item_data' => []
                ];
                foreach ($log_exercises->template_log_items as $log_items)
                {
                    if ($log_items->is_distance)
                    {
                        $type = 'D';
                        $value = $log_items->logtempitem_distance;
                    }
                    elseif ($log_items->is_time)
                    {
                        $type = 'T';
                        $value = $log_items->logtempitem_time;
                    }
                    elseif ($log_items->is_current_rm)
                    {
                        $type = 'RM';
                        $value = $log_items->current_rm;
                    }
                    elseif ($log_items->is_percent_1rm)
                    {
                        $type = 'P';
                        $value = $log_items->percent_1rm;
                    }
                    else
                    {
                        $type = 'W';
                        if ($log_items->is_bw)
                        {
                            $value = 'BW';
                        }
                        else
                        {
                            $value = $log_items->logtempitem_weight;
                        }
                    }
                    $exercise_data['item_data'][] = [
                        'value' => $value,
                        'plus' => $log_items->logtempitem_plus_weight,
                        'reps' => $log_items->logtempitem_reps,
                        'sets' => $log_items->logtempitem_sets,
                        'rpe' => $log_items->logtempitem_rpe,
                        'comment' => $log_items->logtempitem_comment,
                        'warmup' => $log_items->is_warmup,
                        'type' => $type
                    ];
                }
                $log_data['exercise_data'][] = $exercise_data;
            }
            $json_data[] = $log_data;
        }
        return json_encode($json_data);
    }

    public static function saveTemplateLogs($request, $template_id)
    {
        for ($i = 0; $i < count($request->input('log_name')); $i++)
        {
            // save the log
            $log = new \App\Template_log;
            $log->template_id = $template_id;
            $log->template_log_name = $request->input('log_name')[$i];
            $log->template_log_week = $request->input('log_week')[$i];
            $log->template_log_day = $request->input('log_day')[$i];
            $log->has_fixed_values = true;
            $log->save();
            $log_id = $log->template_log_id;
            if (isset($request->input('exercise_name')[$i]))
            {
                for ($j = 0; $j < count($request->input('exercise_name')[$i]); $j++)
                {
                    // save the exercise
                    $exercise = new \App\Template_log_exercise;
                    $exercise->template_log_id = $log_id;
                    // find exercise
                    $exercise_data = \App\Template_exercises::firstOrCreate(['texercise_name' => $request->input('exercise_name')[$i][$j]]);
                    $exercise->texercise_id = $exercise_data->texercise_id;
                    $exercise->texercise_name = $request->input('exercise_name')[$i][$j];
                    $exercise->logtempex_order = $j;
                    $exercise->save();
                    $exercise_id = $exercise->logtempex_id;
                    if (isset($request->input('item_type')[$i][$j]))
                    {
                        for ($k = 0; $k < count($request->input('item_type')[$i][$j]); $k++)
                        {
                            // save the item
                            $item = new \App\Template_log_items;
                            $item->template_log_id = $log_id;
                            $item->logtempex_id = $exercise_id;
                            $item->texercise_id = $exercise_data->texercise_id;
                            switch ($request->input('item_type')[$i][$j][$k])
                            {
                                case 'W':
                                    $item->is_weight = true;
                                    if ($request->input('item_value')[$i][$j][$k] == 'BW')
                                    {
                                        $item->is_bw = true;
                                    }
                                    else
                                    {
                                        $item->logtempitem_weight = $request->input('item_value')[$i][$j][$k];
                                    }
                                    break;
                                case 'P':
                                    $item->is_percent_1rm = true;
                                    $item->percent_1rm = $request->input('item_value')[$i][$j][$k];
                                    if ($log->has_fixed_values)
                                    {
                                        $log->has_fixed_values = false;
                                        $log->save();
                                    }
                                    break;
                                case 'RM':
                                    $item->is_current_rm = true;
                                    $item->current_rm = $request->input('item_value')[$i][$j][$k];
                                    if ($log->has_fixed_values)
                                    {
                                        $log->has_fixed_values = false;
                                        $log->save();
                                    }
                                    break;
                                case 'T':
                                    $item->is_time = true;
                                    $item->logtempitem_time = $request->input('item_value')[$i][$j][$k];
                                    break;
                                case 'D':
                                    $item->is_distance = true;
                                    $item->logtempitem_distance = $request->input('item_value')[$i][$j][$k];
                                    break;
                            }
                            if (floatval($request->input('item_plus')[$i][$j][$k]) > 0)
                            {
                                $item->has_plus_weight = true;
                                $item->logtempitem_plus_weight = $request->input('item_plus')[$i][$j][$k];
                            }
                            if (floatval($request->input('item_rpe')[$i][$j][$k]) > 0)
                            {
                                $item->is_rpe = true;
                                $item->logtempitem_rpe = $request->input('item_rpe')[$i][$j][$k];
                            }
                            $item->logtempitem_reps = $request->input('item_reps')[$i][$j][$k];
                            $item->logtempitem_sets = $request->input('item_sets')[$i][$j][$k];
                            $item->logtempitem_comment = $request->input('item_comment')[$i][$j][$k];
                            $item->is_warmup = (isset($request->input('item_warmup')[$i][$j][$k]) ? true : false);
                            $item->logtempitem_order = $k;
                            $item->logtempex_order = $j;
                            $item->save();
                        }
                    }
                }
            }
        }
    }
}