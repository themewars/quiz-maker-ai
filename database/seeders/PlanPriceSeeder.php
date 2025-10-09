<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = Plan::all();
        $currencies = Currency::all();

        foreach ($plans as $plan) {
            foreach ($currencies as $currency) {
                // Skip if price already exists
                if (PlanPrice::where('plan_id', $plan->id)->where('currency_id', $currency->id)->exists()) {
                    continue;
                }

                // Calculate price based on currency
                $price = $this->calculatePrice($plan->price, $currency->code);
                
                PlanPrice::create([
                    'plan_id' => $plan->id,
                    'currency_id' => $currency->id,
                    'price' => $price,
                    'payment_gateway_plan_id' => $plan->payment_gateway_plan_id, // Can be updated later
                ]);
            }
        }
    }

    private function calculatePrice($basePrice, $currencyCode): float
    {
        // Base price is in INR, convert to other currencies
        $exchangeRates = [
            'INR' => 1.0,      // Base currency
            'USD' => 0.012,    // 1 INR = 0.012 USD (approximate)
            'EUR' => 0.011,    // 1 INR = 0.011 EUR (approximate)
            'GBP' => 0.0095,   // 1 INR = 0.0095 GBP (approximate)
            'JPY' => 1.8,      // 1 INR = 1.8 JPY (approximate)
            'CAD' => 0.016,    // 1 INR = 0.016 CAD (approximate)
            'AUD' => 0.018,    // 1 INR = 0.018 AUD (approximate)
        ];

        $rate = $exchangeRates[$currencyCode] ?? 1.0;
        $convertedPrice = $basePrice * $rate;

        // Round to 2 decimal places
        return round($convertedPrice, 2);
    }
}