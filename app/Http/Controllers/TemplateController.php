<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Exercise;
use App\Template;

class TemplateController extends Controller
{
	public function home()
	{
		$templates = Template::all();
		return view('templates.index', compact('templates'));
	}

	public function viewTemplate($template_id)
	{
		$template = Template::with('template_logs.template_log_exercises.template_log_items')->where('template_id', $template_id)->firstorfail();
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

	public function getBuildTemplate()
	{

	}

	public function postBuildTemplate(Request $request)
	{
		// check inputs
		//check log_id is valid
		if ()
		{
			
		}
		foreach ($request->exercise as $key => $exercise)
		{
			if ($exercise == 0 && $request->weight[$key] == '')
			{
				return redirect()->back()
						->withInput()
						->with(['flash_message' => 'Please enter weight or select an exercise to generate the workout from', 'flash_message_type' => 'danger', 'flash_message_important' => true]);
			}
			else
			{
				// check exercise exists
			}
		}

	}
}
