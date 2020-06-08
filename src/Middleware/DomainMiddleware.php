<?php
namespace Tenancy\Middleware;

use Closure;
use Illuminate\Http\Request;

class DomainMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $domain = $request->getHost();

        $activeTenant = tenant()->getClass()::whereDomain($domain)->first();

        tenant()->switchTo($activeTenant);

        return $next($request);
    }
}
