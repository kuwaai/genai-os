<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use App\Http\Controllers\ProfileController;

class CheckAllowedIP
{
    public function handle(Request $request, Closure $next)
    {
        $allowedCIDRs = array_filter(explode(',', env('ALLOWED_IPS', '')), 'strlen');
        $ip_allowed = !$allowedCIDRs || ProfileController::isIPInCIDRList(request()->ip(), $allowedCIDRs);
        if ($ip_allowed) {
            return $next($request);
        }

        return redirect()->route('errors.ipnotallowed');
    }
}
