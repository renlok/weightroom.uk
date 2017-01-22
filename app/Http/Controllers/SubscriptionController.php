<?php

namespace App\Http\Controllers;

use Auth;
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
        Auth::user()->newSubscription('weightroom_gold', 'monthly')->create($request->input('stripeToken'));
        // TODO send email
        return redirect()
              ->route('userPremium')
              ->with([
                  'flash_message' => 'Your are now a premium member.'
              ]);
    }

    public function getCancelPremium()
    {
        Auth::user()->subscription('weightroom_gold')->cancel();
        // TODO send email
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
          // TODO send email
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
