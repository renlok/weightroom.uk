<?php

namespace App\Http\Middleware;

use DB;
use Closure;

class InjectPasswordGrantCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($request->grant_type == 'password') {
            $client = DB::table('oauth_clients')
                ->where('password_client', 1)
                ->first();

            $request->request->add([
                'client_id' => $client->id,
                'client_secret' => $client->secret,
            ]);
        }

        return $next($request);
    }
}