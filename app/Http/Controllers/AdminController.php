<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use Carbon\Carbon;

use App\Console\Commands\ImportFiles;
use App\Console\Commands\GlobalStats;
use App\Console\Commands\CleanJunk;

use App\Admin;
use App\Template;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            AdminController::adminCheck();
            return $next($request);
        });
    }

    public static function adminCheck()
    {
        if (Auth::user()->user_id != 1) abort(404);
    }

    public function home()
    {
        $cron_count = DB::table('import_data')->select(DB::raw('COUNT(import_id) as cron_count'))->value('cron_count');
        return view('admin.index', compact('cron_count'));
    }

    public function getStats()
    {
        $stats = DB::table('global_stats')->orderBy('gstat_date', 'asc')->get();
        return view('admin.stats', compact('stats'));
    }

    public function forceStats()
    {
        $stats = new GlobalStats;
        $stats->handle();
        return redirect()
            ->route('adminHome')
            ->with(['flash_message' => "Collected stats new data"]);
    }

    public function cleanJunk()
    {
        $stats = new CleanJunk;
        $stats->handle();
        return redirect()
            ->route('adminHome')
            ->with(['flash_message' => "Cleaned database"]);
    }

    public function forceRebuildExercisePRTable($exercise_id)
    {
        \App\Extend\PRs::rebuildExercisePRs($exercise_id);
        return redirect()
            ->route('adminHome')
            ->with(['flash_message' => "Exercise ID: $exercise_id PR history rebuilt"]);
    }

    public function cronImport()
    {
        $import = new ImportFiles;
        $import->handle();
        return redirect()
            ->route('adminHome')
            ->with(['flash_message' => "Import Completed"]);
    }

    public function getSettings()
    {
        $settings = Admin::getSettings();
        return view('admin.settings', compact('settings'));
    }

    public function postSettings(Request $request)
    {
        // invites_enabled
        Admin::where('setting_name', 'invites_enabled')->update(['setting_value' => $request->input('invites_enabled')]);

        return redirect()
            ->route('adminSettings')
            ->with(['flash_message' => "Settings updated"]);
    }

    public function getListTemplates()
    {
        $templates = Template::all();
        return view('admin.listTemplates', compact('templates'));
    }

    public function getAddTemplate()
    {
        $json_data = json_encode([]);
        $template_id = 0;
        $template_name = '';
        $template_description = '';
        $template_type = '';
        return view('admin.editLogTemplate', compact('json_data', 'template_id', 'template_name', 'template_description', 'template_type'));
    }

    public function postAddTemplate(Request $request)
    {
        // TODO: validate inputs
        // save the template
        $template = new \App\Template;
        $template->template_name = $request->input('template_name');
        $template->template_description = $request->input('template_description');
        $template->template_type = $request->input('template_type');
        $template->save();
        $template_id = $template->template_id;
        AdminController::saveTemplateLogs($request, $template_id);
        return redirect()
            ->route('adminListTemplates')
            ->with(['flash_message' => "Template added"]);
    }

    public function getEditTemplate($template_id)
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
        $json_data = json_encode($json_data);
        $template_name = $template->template_name;
        $template_description = $template->template_description;
        $template_type = $template->template_type;
        return view('admin.editLogTemplate', compact('json_data', 'template_id', 'template_name', 'template_description', 'template_type'));
    }

    public function postEditTemplate(Request $request, $template_id)
    {
        // delete old data
        $template_logs = DB::table('template_logs')->where('template_id', $template_id)->pluck('template_log_id')->all();
        DB::table('template_logs')->whereIn('template_log_id', $template_logs)->delete();
        DB::table('template_log_exercises')->whereIn('template_log_id', $template_logs)->delete();
        DB::table('template_log_items')->whereIn('template_log_id', $template_logs)->delete();
        // update template
        $template = Template::where('template_id', $template_id)->update([
            'template_name' => $request->input('template_name'),
            'template_description' => $request->input('template_description'),
            'template_type' => $request->input('template_type'),
        ]);
        AdminController::saveTemplateLogs($request, $template_id);
        return redirect()
            ->route('adminListTemplates')
            ->with(['flash_message' => "Template updated"]);
    }

    private static function saveTemplateLogs($request, $template_id)
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

    public function getDeleteTemplate($template_id)
    {
        DB::table('templates')->where('template_id', $template_id)->delete();
        $template_logs = DB::table('template_logs')->where('template_id', $template_id)->pluck('template_log_id')->all();
        DB::table('template_log_items')->whereIn('template_log_id', $template_logs)->delete();
        DB::table('template_log_exercises')->whereIn('template_log_id', $template_logs)->delete();
        DB::table('template_logs')->where('template_id', $template_id)->delete();
        return redirect()
                ->route('adminListTemplates')
                ->with([
                    'flash_message' => 'Template deleted.',
                    'flash_message_type' => 'danger'
                ]);
    }
}
