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
                $plan->current_price = $planPrice ? $planPrice->price : $plan->price;
                $plan->current_currency = $currentCurrency;
                
                // Get all currency prices for this plan
                $plan->all_prices = $plan->prices()->with('currency')->get()->keyBy('currency_id');
                
                return $plan;
            });
            
        $faqs = Faq::where('status', 1)->get();
            
        return view('pricing.index', compact('plans', 'faqs', 'allCurrencies', 'currentCurrency'));
    }
}
