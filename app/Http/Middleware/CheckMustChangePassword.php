<?php

namespace MineStats\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\UnauthorizedException;
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
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            if ($request->ajax()) {
                throw new AuthorizationException('Must change password.');
            }

            return redirect()->guest(route('account'));
        }

        return $next($request);
    }
}
