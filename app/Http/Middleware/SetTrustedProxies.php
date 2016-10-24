<?php

namespace MineStats\Http\Middleware;

use Closure;

class SetTrustedProxies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $trustedProxies = config('minestats.trusted_proxies');
        if (!empty($trustedProxies)) {
            $request->setTrustedProxies($trustedProxies);
        }

        return $next($request);
    }
}
