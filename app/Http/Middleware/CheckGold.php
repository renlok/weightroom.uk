<?php

namespace App\Http\Middleware;

use Closure;

class CheckGold
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
        if (!$request->user()->subscribed('weightroom_gold')) {
            return response()->view('common.goldOnly');
        }

        return $next($request);
    }
}
