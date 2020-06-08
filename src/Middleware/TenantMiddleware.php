<?php
namespace App\Modules\Tenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Modules\Tenancy\Tenant;
use ActiveTenant;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (tenant()->get()->code) {
            return $next($request);
        }

        return redirect('/');
    }
}
