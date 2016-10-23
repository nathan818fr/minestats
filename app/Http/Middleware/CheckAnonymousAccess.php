<?php

namespace MineStats\Http\Middleware;

use Closure;
use Auth;

class CheckAnonymousAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!config('minestats.allow_anonymous') && !Auth::check()) {
            return redirect()->guest('login');
        }

        return $next($request);
    }
}
