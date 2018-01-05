<?php

namespace App\Http\Middleware;

use Closure;
use App\Template;

class OwnTemplate
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
        if (Template::where('template_id', $request->route()->parameters()['template_id'])->where('user_id', $request->user()->user_id)->first() == null)
        {
            return redirect()
                ->route('templatesHome');
        }
        return $next($request);
    }
}
