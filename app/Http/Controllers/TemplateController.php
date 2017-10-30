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
use App\Template_purchase;
use App\User;
use App\Extend\PRs;
use App\Extend\Log_control;
use Auth;
use Validator;
use Stripe\Account as StripeAccount;

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
        $purchased_on = null;
        if ($template->template_charge > 0)
        {
            $purchased_on = Template_purchase::where('user_id', Auth::user()->user_id)->where('template_id', $template_id)->value('created_at');
            if ($purchased_on == null)
            {
                return TemplateController::getTemplateSales($template);
            }
        }
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
        $is_active = (User::activeTemplate(Auth::user()->user_id) == $template_id);
        return view('templates.view', compact('template', 'template_exercises', 'exercises', 'purchased_on', 'is_active'));
    }

    public function getTemplateSales($template)
    {
        return view('templates.sale', compact('template'));
    }

    public function getTemplateSaleProcess($template_id)
    {
        $template = Template::where('template_id', $template_id)->firstorfail();
        if ($template->template_charge > 0)
        {
            $purchased_on = Template_purchase::where('user_id', Auth::user()->user_id)->where('template_id', $template_id)->value('created_at');
            if ($purchased_on != null)
            {
                return redirect()
                    ->route('viewTemplate', ['template_id' => $template_id])
                    ->with([
                        'flash_message' => 'You cannot buy this template',
                        'flash_message_type' => 'danger',
                        'flash_message_important' => true
                    ]);
            }
        }
        else
        {
            return redirect()
                ->route('viewTemplate', ['template_id' => $template_id])
                ->with([
                    'flash_message' => 'You cannot buy this template',
                    'flash_message_type' => 'danger',
                    'flash_message_important' => true
                ]);
        }
        return view('templates.saleProcess', compact('template'));
    }

    public function postTemplateSaleProcess($template_id, Request $request)
    {
        $template = Template::where('template_id', $template_id)->firstorfail();
        $stripID = User::find($template->user_id)->value('stripe_custom_id');
        if ($stripID == '')
        {
            return redirect()
                ->route('templatesHome')
                ->with(['flash_message' => 'You cannot purchase that workout at this time.', 'flash_message_type' => 'danger', 'flash_message_important' => true]);
        }
        Auth::user()->charge($template->template_charge * 100, [
            'destination' => [
                'account' => $stripID
            ],
            'currency' => 'usd', //TODO add option to change this
            'application_fee' => floor(($template->template_charge * 100) * ((env('TEMPLATE_PERCENT_FEE', 10)) / 100)),
            'source' => $request->input('stripeToken')
        ]);
        $purchase = new Template_purchase();
        $purchase->user_id = Auth::user()->user_id;
        $purchase->template_id = $template_id;
        $purchase->template_purchase_charge = $template->template_charge;
        $purchase->save();
        return redirect()
            ->route('viewTemplate', ['template_id' => $template_id])
            ->with(['flash_message' => 'Thankyou for your purchase.', 'flash_message_important' => true]);
    }

    public function getSetupPayAccount()
    {
        if (Auth::user()->stripe_custom_id != null)
        {
            $customer = StripeAccount::retrieve(Auth::user()->stripe_custom_id, User::getStripeKey());
        }
        else
        {
            $customer = null;
        }
        return view('user.setupPayAccount', compact('customer'));
    }

    public function postSetupPayAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required|in:AU,AT,BE,CA,DK,FI,FR,DE,HK,IE,IT,JP,LU,NE,NZ,NO,PT,SG,ES,SE,CH,GB,US'
        ]);
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        $user = User::where('user_id', Auth::user()->user_id)->first();
        if ($user->stripe_custom_id != null) {
            $customer = StripeAccount::retrieve(Auth::user()->stripe_custom_id, User::getStripeKey());
        } else {
            $customer = StripeAccount::create(
                [
                    "country" => $request->input('country'),
                    "type" => "custom",
                    "email" => Auth::user()->user_email
                ], User::getStripeKey()
            );
        }
        $customer->legal_entity->type = $request->input('account-type');
        $customer->legal_entity->first_name = $request->input('first-name');
        $customer->legal_entity->last_name = $request->input('last-name');
        $customer->legal_entity->dob->day = $request->input('day');
        $customer->legal_entity->dob->month = $request->input('month');
        $customer->legal_entity->dob->year = $request->input('year');
        $customer->legal_entity->address->line1 = $request->input('address-line1');
        $customer->legal_entity->address->city = $request->input('address-city');
        $customer->legal_entity->address->postal_code = $request->input('postal-code');
        if ($request->input('account-type') == 'company')
        {
            $customer->legal_entity->business_name = $request->input('business-name');
            $customer->legal_entity->business_tax_id = $request->input('tax-id');
            $customer->legal_entity->additional_owners = '';
            $customer->legal_entity->personal_address->city = '';
            $customer->legal_entity->personal_address->line1 = '';
            $customer->legal_entity->personal_address->postal_code = '';
        }
        $customer->tos_acceptance->date = time();
        $customer->tos_acceptance->ip = $_SERVER['REMOTE_ADDR'];
        $customer->save();
        $user->stripe_custom_id = $customer->id;
        $user->save();
        return redirect()
            ->back()
            ->with(['flash_message' => 'Account created.']);
    }

    public function getSetupPayAccountBank()
    {
        $customer = StripeAccount::retrieve(Auth::user()->stripe_custom_id, User::getStripeKey());
        return view('user.setupPayAccountBank', compact('customer'));
    }

    public function postSetupPayAccountBank(Request $request)
    {
        $user = User::where('user_id', Auth::user()->user_id)->first();
        if ($user->stripe_custom_id != null) {
            $customer = StripeAccount::retrieve(Auth::user()->stripe_custom_id, User::getStripeKey());
            $customer->external_account = $request->input('stripeToken');
            $customer->save();
            return redirect()->route('setupPayAccount')->with(['flash_message' => 'Bank account has been added to your account.']);
        } else {
            return redirect()->route('setupPayAccount');
        }
    }

    public function postBuildTemplate(Request $request)
    {
        // TODO: check inputs
        // TODO: check log_id is valid
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
