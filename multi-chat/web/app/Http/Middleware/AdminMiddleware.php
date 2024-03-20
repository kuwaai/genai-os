<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $config): Response
    {
        if (
            auth()->check() &&
            auth()
                ->user()
                ->hasPerm($config)
        ) {
            return $next($request);
        } elseif ($config == 'tab_Room') {
            return redirect('/');
        }

        abort(403, 'Unauthorized action.');
    }
}
