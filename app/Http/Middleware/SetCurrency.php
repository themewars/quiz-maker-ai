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
        // If admin enforces home currency on homepage, apply it
        $enforce = (bool) (getSetting()->enforce_home_currency ?? false);
        if ($enforce && $this->isHomePage($request)) {
            $forced = getSetting()->home_currency_code ?? null;
            if ($forced && $this->isValidCurrency($forced)) {
                session(['currency' => strtoupper($forced)]);
                app()->instance('currency', strtoupper($forced));
                return $next($request);
            }
        }

        // Otherwise, get currency from session, cookie, or detect from IP
        $currencyCode = $this->getCurrencyCode($request);
        
        // Set currency in session and app
        session(['currency' => $currencyCode]);
        app()->instance('currency', $currencyCode);
        
        return $next($request);
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
        $ip = $request->ip();
        
        // For localhost/testing, default to INR
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return 'INR';
        }

        try {
            // Use a free GeoIP service to detect country
            $response = file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");
            $data = json_decode($response, true);
            
            if (isset($data['countryCode']) && $data['countryCode'] === 'IN') {
                return 'INR';
            }
        } catch (\Exception $e) {
            // Fallback to USD if GeoIP fails
        }

        return 'USD';
    }

    private function isValidCurrency(string $currencyCode): bool
    {
        return Currency::where('code', $currencyCode)->exists();
    }
}