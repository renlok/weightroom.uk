<?php

namespace App\Http\Middleware;

use Closure;
use App\Log;

class RedirectLogifNotExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Log::isValid($request->route()->parameters()['date'], $request->user()->user_id))
        {
            return redirect()
                    ->route('newLog', ['date' => $request->route()->parameters()['date']]);
        }
        return $next($request);
    }
}
