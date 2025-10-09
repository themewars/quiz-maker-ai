<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Plan;
use App\Models\Currency;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $currentCurrency = getCurrentCurrency();
        $allCurrencies = getAllCurrencies();
        
        $plans = Plan::with(['prices.currency', 'currency'])
            ->orderBy('price')
            ->get()
            ->map(function ($plan) use ($currentCurrency) {
                // Get price for current currency
                $planPrice = $plan->prices()->where('currency_id', $currentCurrency->id)->first();
                if ($planPrice) {
                    $plan->current_price = $planPrice->price;
                } else {
                    // Fallback convert from base (assume base INR)
                    $plan->current_price = $this->convertFromInr((float) $plan->price, $currentCurrency->code);
                }
                $plan->current_currency = $currentCurrency;
                
                // Get all currency prices for this plan
                $plan->all_prices = $plan->prices()->with('currency')->get()->keyBy('currency_id');
                
                return $plan;
            });
            
        $faqs = Faq::where('status', 1)->get();

        // Build lightweight planData for JS (avoid complex @json closures in blade)
        $planData = $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'prices' => $plan->prices->map(function ($price) {
                    return [
                        'currency_id' => $price->currency_id,
                        'currency_code' => $price->currency->code,
                        'currency_symbol' => $price->currency->symbol,
                        'price' => $price->price,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
            
        return view('pricing.index', compact('plans', 'faqs', 'allCurrencies', 'currentCurrency', 'planData'));
    }

    private function convertFromInr(float $amountInInr, string $targetCode): float
    {
        $rates = [
            'INR' => 1.0,
            'USD' => 0.012,
            'EUR' => 0.011,
            'GBP' => 0.0095,
            'JPY' => 1.8,
            'CAD' => 0.016,
            'AUD' => 0.018,
        ];
        $rate = $rates[strtoupper($targetCode)] ?? 1.0;
        return round($amountInInr * $rate, 2);
    }
}
