<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for auth routes to prevent 404 errors
        if ($this->isAuthRoute($request)) {
            return $next($request);
        }

        $localeLanguage = Session::get('locale');

        if ($localeLanguage) {
            App::setLocale($localeLanguage);
            return $next($request);
        }

        try {
            $setting = getSetting();
            App::setLocale($setting ? $setting->default_language : 'en');
        } catch (\Exception $e) {
            // Fallback to default language if database is not available
            App::setLocale('en');
        }
        
        return $next($request);
    }

    /**
     * Check if the request is for auth routes
     */
    private function isAuthRoute(Request $request): bool
    {
        $authRoutes = ['login', 'register', 'password/reset', 'email/verify'];
        $path = $request->path();
        
        foreach ($authRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }
        
        return false;
    }
}
