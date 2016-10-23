<?php

namespace MineStats\Http\Middleware;

use Closure;
use Auth;
use Route;

class CheckMustChangePassword
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
        if (Auth::check() && Auth::user()->must_change_password) {
            return redirect()->guest(route('account'));
        }

        return $next($request);
    }
}
