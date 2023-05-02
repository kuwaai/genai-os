<?php

namespace App\Http\Middleware;

use Closure;

class SuppressErrors
{
    public function handle($request, Closure $next)
    {
        error_reporting(0);
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        return $next($request);
    }
}