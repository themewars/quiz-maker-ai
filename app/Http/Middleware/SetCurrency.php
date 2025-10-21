<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrency
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

        try {
            // If admin enforces home currency on homepage, apply it
            $setting = getSetting();
            $enforce = (bool) ($setting->enforce_home_currency ?? false);
            if ($enforce && $this->isHomePage($request)) {
                $forced = $setting->home_currency_code ?? null;
                if ($forced && $this->isValidCurrency($forced)) {
                    session(['currency' => strtoupper($forced)]);
                    app()->instance('currency', strtoupper($forced));
                    return $next($request);
                }
            }
        } catch (\Exception $e) {
            // Fallback if database is not available
        }

        // Otherwise, get currency from session, cookie, or detect from IP
        $currencyCode = $this->getCurrencyCode($request);
        
        // Set currency in session and app
        session(['currency' => $currencyCode]);
        app()->instance('currency', $currencyCode);
        
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

    private function isHomePage(Request $request): bool
    {
        $path = trim($request->path(), '/');
        return $path === '' || $path === 'home';
    }

    private function getCurrencyCode(Request $request): string
    {
        // 1. Check if currency is explicitly set in request
        if ($request->has('currency')) {
            $currencyCode = strtoupper($request->get('currency'));
            if ($this->isValidCurrency($currencyCode)) {
                return $currencyCode;
            }
        }

        // 2. Check session
        if (session()->has('currency')) {
            $currencyCode = session('currency');
            if ($this->isValidCurrency($currencyCode)) {
                return $currencyCode;
            }
        }

        // 3. Check cookie
        if ($request->hasCookie('currency')) {
            $currencyCode = $request->cookie('currency');
            if ($this->isValidCurrency($currencyCode)) {
                return $currencyCode;
            }
        }

        // 4. Auto-detect from IP (India = INR, Others = USD)
        return $this->detectCurrencyFromIP($request);
    }

    private function detectCurrencyFromIP(Request $request): string
    {
        // 1) Prefer proxy/CDN headers (Cloudflare, common proxies)
        $country = strtoupper((string) (
            $request->headers->get('CF-IPCountry')
            ?? $request->headers->get('X-Country-Code')
            ?? $request->headers->get('X-AppEngine-Country')
            ?? ''
        ));
        if ($country === 'IN') {
            return 'INR';
        } elseif ($country !== '') {
            return 'USD';
        }

        // 2) Localhost/dev defaults
        $ip = $request->ip();
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return 'INR';
        }

        // 3) External lookup with short timeout (best-effort)
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 1.5,
                ],
            ]);
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode", false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['countryCode']) && strtoupper($data['countryCode']) === 'IN') {
                    return 'INR';
                }
            }
        } catch (\Throwable $e) {
            // ignore and fallback
        }

        // 4) Final fallback - allow env override
        $fallback = strtoupper((string) env('DEFAULT_CURRENCY', 'USD'));
        return $fallback === 'INR' ? 'INR' : 'USD';
    }

    private function isValidCurrency(string $currencyCode): bool
    {
        return Currency::where('code', $currencyCode)->exists();
    }
}