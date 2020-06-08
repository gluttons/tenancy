<?php
namespace Tenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tenancy\Tenant;
use ActiveTenant;

class HomeMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!tenant()->get()->code) {
            return $next($request);
        }

        return redirect('/');
    }
}
