<?php

namespace App\Actions\Subscription;

use App\Enums\PlanFrequency;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCurrentSubscription
{
    use AsAction;

    public function handle(): array
    {
        $currentPlan = Subscription::where('user_id', auth()->id())->where('status', SubscriptionStatus::ACTIVE->value)->first();

        if (!empty($currentPlan) && !$currentPlan->isExpired()) {
            // Convert current plan to current session currency for consistency
            $currentCurrency = getCurrentCurrency();
            $originalCurrency = $currentPlan['plan']['currency'];
            
            // Use current session currency instead of original plan currency
            $currentPlan['currency_icon'] = $currentCurrency->symbol;
            $currentPlan['currency_code'] = $currentCurrency->code;
            
            $currentPlan['used_days'] = round(abs(now()->diffInDays($currentPlan['starts_at'])));

            if ($currentPlan['plan_frequency'] == PlanFrequency::MONTHLY->value) {
                $currentPlan['total_days'] = 30;
            } elseif ($currentPlan['plan_frequency'] == PlanFrequency::WEEKLY->value) {
                $currentPlan['total_days'] = 7;
            } elseif ($currentPlan['plan_frequency'] == PlanFrequency::YEARLY->value) {
                $currentPlan['total_days'] = 365;
            }

            $currentPlan['remaining_days'] = round($currentPlan['total_days'] - $currentPlan['used_days']);
            $perDayPrice = round($currentPlan['plan_amount'] / $currentPlan['total_days'], 2);
            $currentPlan['remaining_balance'] = round($currentPlan['plan_amount'] - ($perDayPrice * $currentPlan['used_days']));
            $currentPlan['used_balance'] = round($currentPlan['plan_amount'] - $currentPlan['remaining_balance']);
            
            // Convert amounts to current currency if different from original
            if ($originalCurrency->code !== $currentCurrency->code) {
                $conversionRate = $this->getCurrencyConversionRate($originalCurrency->code, $currentCurrency->code);
                $currentPlan['plan_amount'] = round($currentPlan['plan_amount'] * $conversionRate, 2);
                $currentPlan['remaining_balance'] = round($currentPlan['remaining_balance'] * $conversionRate, 2);
                $currentPlan['used_balance'] = round($currentPlan['used_balance'] * $conversionRate, 2);
            }
        } else {
            return $currentPlan = [];
        }

        return $currentPlan->toArray();
    }
    
    private function getCurrencyConversionRate($fromCurrency, $toCurrency)
    {
        // Simple conversion rates (you can make this dynamic)
        $rates = [
            'INR' => ['USD' => 0.012, 'EUR' => 0.011],
            'USD' => ['INR' => 83.0, 'EUR' => 0.92],
            'EUR' => ['INR' => 90.0, 'USD' => 1.09],
        ];
        
        return $rates[$fromCurrency][$toCurrency] ?? 1.0;
    }
}
