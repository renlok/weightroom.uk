<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Template;

use DB;

class TemplateController extends Controller
{
	public function home()
	{
		$templates = Template::all();
		return view('templates.index', compact('templates'));
	}

	public function viewTemplate($template_id)
	{
		DB::enableQueryLog();
		$template = Template::with('template_logs.template_log_exercises.template_log_items')->where('template_id', $template_id)->firstorfail();
		print_r(DB::getQueryLog());
		return view('templates.view', compact('template'));
	}

	public function getBuildTemplate()
	{

	}

	public function postBuildTemplate(Request $request)
	{

	}
}
