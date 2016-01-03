<?php

namespace App\Http\Middleware;

use Closure;
use App\Log;

class RedirectLogifExists
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
        if (Log::scopeGetlog($request->route()->parameters()['date'], $request->user()->user_id)->count() > 0)
        {
            return redirect()
                    ->route('editLog', ['date' => $request->route()->parameters()['date']]);
        }
        return $next($request);
    }
}
