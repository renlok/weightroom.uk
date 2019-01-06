<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\TemplateRequest;
use Illuminate\Http\Response;

use Auth;
use Carbon\Carbon;
use DB;
use File;

use App\Console\Commands\GenerateCleanNames;
use App\Console\Commands\ImportFiles;
use App\Console\Commands\GlobalStats;
use App\Console\Commands\CleanJunk;

use App\Admin;
use App\Template;
use App\User;
use App\Extend\Templates;

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

    public function forceCleanNames()
    {
        $stats = new GenerateCleanNames();
        $stats->handle();
        return redirect()
            ->route('adminHome')
            ->with(['flash_message' => "Rebuilt Clean Names"]);
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
        $template_charge = 0;
        $template_is_lp = 0;
        $template_is_public = 1;
        return view('admin.editLogTemplate', compact('json_data', 'template_id', 'template_name', 'template_description', 'template_type', 'template_charge', 'template_is_lp', 'template_is_public'));
    }

    public function postAddTemplate(TemplateRequest $request)
    {
        // save the template
        $template = new \App\Template;
        $template->template_name = $request->input('template_name');
        $template->template_description = $request->input('template_description');
        $template->template_type = $request->input('template_type');
        $template->template_charge = $request->input('template_charge');
        $template->template_is_lp = $request->input('template_is_lp', 0);
        $template->template_is_public = $request->input('template_is_public', 0);
        $template->save();
        $template_id = $template->template_id;
        Templates::saveTemplateLogs($request, $template_id);
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
        $template_name = $template->template_name;
        $template_description = $template->template_description;
        $template_type = $template->template_type;
        $template_charge = $template->template_charge;
        $template_is_lp = $template->template_is_lp;
        $template_is_public = $template->template_is_public;
        $json_data = Templates::loadJSONData($template);
        return view('admin.editLogTemplate', compact('json_data', 'template_id', 'template_name', 'template_description', 'template_type', 'template_charge', 'template_is_lp', 'template_is_public'));
    }

    public function postEditTemplate(TemplateRequest $request, $template_id)
    {
        // delete old data
        $template_logs = DB::table('template_logs')->where('template_id', $template_id)->pluck('template_log_id')->all();
        DB::table('template_logs')->whereIn('template_log_id', $template_logs)->delete();
        DB::table('template_log_exercises')->whereIn('template_log_id', $template_logs)->delete();
        DB::table('template_log_items')->whereIn('template_log_id', $template_logs)->delete();
        // update template
        Template::where('template_id', $template_id)->update([
            'template_name' => $request->input('template_name'),
            'template_description' => $request->input('template_description'),
            'template_type' => $request->input('template_type'),
            'template_charge' => $request->input('template_charge'),
            'template_is_lp' => $request->input('template_is_lp', 0),
            'template_is_public' => $request->input('template_is_public', 0),
        ]);
        Templates::saveTemplateLogs($request, $template_id);
        return redirect()
            ->route('adminListTemplates')
            ->with(['flash_message' => "Template updated"]);
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

    public function getViewLogs($raw = 0, $log = 'laravel')
    {
        $log_path = storage_path() . '/logs/';
        $files = glob($log_path . '*.log');
        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        $files = array_values($files);
        foreach ($files as $k => $file) {
            preg_match('/(.*)\.log/', basename($file), $matches);
            $files[$k] = $matches[1];
        }
        try
        {
            $log_contents = File::get($log_path . $log . '.log');
        }
        catch (Illuminate\Filesystem\FileNotFoundException $exception)
        {
            $log_contents = "The file doesn't exist";
        }
        return view('admin.viewLogs', compact('files', 'log_contents', 'log', 'raw'));
    }

    public function cleanLogFile($log)
    {
        try
        {
            if ($log == 'laravel') {
                File::put(storage_path() . '/logs/' . $log . '.log', '');
            } else {
                File::delete(storage_path() . '/logs/' . $log . '.log');
            }
        }
        catch (Illuminate\Filesystem\FileNotFoundException $exception)
        {
            return redirect()
                ->route('adminViewLogs')
                ->with([
                    'flash_message' => 'No such file.',
                    'flash_message_type' => 'danger'
                ]);
        }
        return redirect()
            ->route('adminViewLogs');
    }

    public function downloadLogFile($log)
    {
        try
        {
            $content = File::get(storage_path() . '/logs/' . $log . '.log');
            $headers = [
                'Content-type' => 'text/plain',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $log . '.log'),
                'Content-Length' => sizeof($content)
            ];

            // make a response, with the content, a 200 response code and the headers
            return Response::make($content, 200, $headers);
        }
        catch (Illuminate\Filesystem\FileNotFoundException $exception)
        {
            return redirect()
                ->route('adminViewLogs')
                ->with([
                    'flash_message' => 'No such file.',
                    'flash_message_type' => 'danger'
                ]);
        }
        return redirect()
            ->route('adminViewLogs');
    }

    public function getListUsers()
    {
        $users = User::paginate(50);
        return view('admin.listUsers', compact('users'));
    }

    public function shadowBanUser(Request $request)
    {
        $user = User::where('user_id', $request->input('user_id'))->firstOrFail();
        $state = $request->input('state', 1);
        $user->user_shadowban = $state;
        $user->save();
        return redirect()
            ->route('adminListUsers')
            ->with([
                'flash_message' => 'User #'.$request->input('user_id').' has been '.($state ? '' : 'un').'shadow banned',
                'flash_message_type' => 'danger'
            ]);
    }
}
