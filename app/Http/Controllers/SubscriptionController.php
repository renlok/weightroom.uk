<?php

namespace App\Http\Controllers;

use Auth;
use Mail;
use App\Mail\SubscriptionSuccess;
use App\Mail\SubscriptionCancelled;
use App\Mail\SubscriptionRestarted;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function getPremium()
    {
        return view('user.premium');
    }

    public function postPremium(Request $request)
    {
        Auth::user()->newSubscription('weightroom_gold', 'weightroom_gold')->create($request->input('stripeToken'));
        if (Auth::user()->subscribed('weightroom_gold'))
        {
            Mail::to(Auth::user())->send(new SubscriptionSuccess());
            $message = 'Your are now a premium member.';
        }
        else
        {
            $message = 'Something went wrong with your payment.';
        }
        return redirect()
              ->route('userPremium')
              ->with([
                  'flash_message' => $message
              ]);
    }

    public function getCancelPremium()
    {
        Auth::user()->subscription('weightroom_gold')->cancel();
        Mail::to(Auth::user())->send(new SubscriptionCancelled());
        return redirect()
              ->route('userPremium')
              ->with([
                  'flash_message' => 'Your subscription has been cancelled.'
              ]);
    }

    public function getResumePremium()
    {
      if (Auth::user()->subscription('weightroom_gold')->onGracePeriod()) {
          Auth::user()->subscription('weightroom_gold')->resume();
          Mail::to(Auth::user())->send(new SubscriptionRestarted());
          return redirect()
                ->route('userPremium')
                ->with([
                    'flash_message' => 'Your primium subscription has been restarted.'
                ]);
      } else {
          return redirect()->route('userPremium');
      }
    }
}
