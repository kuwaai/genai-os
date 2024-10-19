<?php

namespace App\Http\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Closure;

class AuthCheck
{
    /**
     * Redirect user if they're required to change password
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Default create token
        if ($request->user()->tokens()->where('name', 'API_Token')->count() != 1) {
            $request->user()->tokens()->where('name', 'API_Token')->delete();
            $request->user()->createToken('API_Token', ['access_api']);
        }
        // Create user dir if not exist
        $user_dir = 'root/homes' . '/' . auth()->id();
        if (!Storage::disk('public')->exists($user_dir)) {
            Storage::disk('public')->makeDirectory($user_dir);
        }

        if ($request->user()) {
            // Force change password
            if ($request->user()->require_change_password) {
                return redirect()->route('change_password');
            }
        }
        return $next($request);
    }
}