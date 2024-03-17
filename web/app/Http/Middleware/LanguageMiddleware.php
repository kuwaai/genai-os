<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class LanguageMiddleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $languages = json_decode(env('LANGUAGES'), true) ?: ['en_us' => 'English (US)', 'zh_tw' => '中文 (繁體)'];
        $locale = session('locale');
    
        if (!empty($locale) && array_key_exists($locale, $languages)) {
            App::setLocale($locale);
        } else {
            $fallbackLocale = config('app.locale');
            if (array_key_exists($fallbackLocale, $languages)) {
                App::setLocale($fallbackLocale);
                session(['locale' => $fallbackLocale]);
            } else {
                // If the fallback locale is not in $languages, use the first language as the default
                reset($languages);
                $defaultLocale = key($languages);
                App::setLocale($defaultLocale);
                session(['locale' => $defaultLocale]);
            }
        }
    
        Cookie::queue('locale', App::getLocale(), 60);
        // Force HTTPS on production
        if (App::environment('production')) {
            $response = $next($request);

            // Add HSTS header
            $response->headers->add([
                'Strict-Transport-Security' => 'max-age=31536000'
            ]);

            return $response;
        }

        return $next($request);
    }
    
    
}
