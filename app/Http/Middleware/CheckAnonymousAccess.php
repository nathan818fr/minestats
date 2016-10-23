<?php

namespace MineStats\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\UnauthorizedException;

class CheckAnonymousAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        if (!config('minestats.allow_anonymous') && !Auth::check()) {
            if ($request->ajax()) {
                throw new AuthenticationException('Unauthenticated.');
            }

            return redirect()->guest('login');
        }

        return $next($request);
    }
}
