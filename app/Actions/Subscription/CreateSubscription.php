<?php

namespace App\Actions\Subscription;

use App\Enums\PlanFrequency;
use App\Enums\SubscriptionStatus;
use App\Mail\AdminManualPaymentMail;
use App\Mail\ManualPaymentGuideMail;
use App\Mail\SubscriptionPaymentSuccessMail;
use App\Models\PaymentSetting;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSubscription
{
    use AsAction;

    public function handle(array $data)
    {
        try {
            DB::beginTransaction();

            $plan = $data['plan'];
            $notes = $data['notes'] ?? null;
            $attachment = $data['attachment'] ?? null;
            $paymentType = $data['payment_type'] ?? null;
            $transactionId = $data['transaction_id'] ?? null;
            $trialDays = $data['trial_days'] ?? $plan['trial_days'];

            // Determine subscription status based on payment type
            $subscriptionStatus = SubscriptionStatus::PENDING->value; // Default to pending for admin approval
            
            // SECURITY FIX: All payments require admin approval regardless of payment method
            // Payment gateways are NOT auto-approved anymore for security
            // This prevents international users from bypassing admin approval
            // if ($paymentType && in_array($paymentType, [Subscription::TYPE_RAZORPAY, Subscription::TYPE_PAYPAL, Subscription::TYPE_STRIPE])) {
            //     $subscriptionStatus = SubscriptionStatus::ACTIVE->value;
            // }

            $subscriptionData = [
                'user_id' => $data['user_id'],
                'plan_id' => $plan['id'],
                'transaction_id' => $transactionId,
                'plan_amount' => $plan['price'],
                'payable_amount' => $plan['price'],
                'plan_frequency' => $plan['frequency'],
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addMonth()->endOfDay(),
                'status' => $subscriptionStatus,
                'notes' => $notes,
                'payment_type' => $paymentType,
            ];
            if ($trialDays != null && $trialDays > 0) {
                $subscriptionData['ends_at'] = Carbon::now()->addDays($plan['trial_days'])->endOfDay();
                $subscriptionData['trial_ends_at'] = Carbon::now()->addDays($trialDays)->endOfDay();
            } else {
                if ($plan['frequency'] == PlanFrequency::MONTHLY->value) {
                    $subscriptionData['ends_at'] = Carbon::now()->addMonth()->endOfDay();
                } elseif ($plan['frequency'] == PlanFrequency::WEEKLY->value) {
                    $subscriptionData['ends_at'] = Carbon::now()->addWeek()->endOfDay();
                } elseif ($plan['frequency'] == PlanFrequency::YEARLY->value) {
                    $subscriptionData['ends_at'] = Carbon::now()->addYear()->endOfDay();
                }
            }

            // Only auto-approve FREE plans (no payment required)
            // Manual payments (TYPE_MANUALLY = 4) should always be PENDING
            if ($paymentType == Subscription::TYPE_FREE) {
                $subscriptionData['payable_amount'] = null;
                $subscriptionData['status'] = SubscriptionStatus::ACTIVE->value;
            }
            
            // Ensure manual payments are always PENDING (security fix)
            if ($paymentType == Subscription::TYPE_MANUALLY) {
                $subscriptionData['status'] = SubscriptionStatus::PENDING->value;
            }

            $currentSubscription = GetCurrentSubscription::run();

            if (!empty($currentSubscription)) {
                $price = $subscriptionData['payable_amount'] - $currentSubscription['remaining_balance'];
                if ($price <= 0) {
                    // Don't override payment_type for manual payments - keep original payment method
                    if ($paymentType != Subscription::TYPE_MANUALLY) {
                        $subscriptionData['payment_type'] = $currentSubscription['payment_type'];
                    }
                    $subscriptionData['payable_amount'] = $price > 0 ? $price : 0;
                    // Even with credit balance, require admin approval for security
                    // $subscriptionData['status'] = SubscriptionStatus::ACTIVE->value; // REMOVED
                } else {
                    $subscriptionData['payable_amount'] = $price > 0 ? $price : 0;
                }
            }

            // Remove automatic approval - all payments now require admin approval
            // This section was causing automatic approval bypassing our security fix

            $subscription = Subscription::create($subscriptionData);

            // Inactive old subscription
            if ($subscription->status == SubscriptionStatus::ACTIVE->value) {
                Subscription::where('user_id', $subscription->user_id)
                    ->whereNot('id', $subscription->id)
                    ->whereIn('status', [SubscriptionStatus::ACTIVE->value])
                    ->update(['status' => SubscriptionStatus::INACTIVE->value]);
            }

            if ($attachment != null && !empty($attachment)) {
                $firstValue = array_shift($attachment);
                $subscription->addMedia($firstValue)->toMediaCollection(Subscription::ATTACHMENT);
            }

            DB::commit();

            // Send Email
            $manualPaymentGuide = PaymentSetting::first()->manual_payment_guide ?? null;
            $user = $subscription->user;
            $adminMailData = [
                'super_admin_msg' => __(":name created request for payment of :price", [
                    'name' => $user->name,
                    'price' => $subscription->plan->currency->symbol  . ' ' . $subscription->payable_amount
                ]),
                'attachment' => $subscription->getFirstMedia(Subscription::ATTACHMENT) ?? '',
                'notes' => $subscription->notes ?? '',
                'id' => $subscription->id,
            ];
            if (! env('DISABLE_PAYMENT_EMAILS', false)) {
                if ($paymentType != null && $paymentType == Subscription::TYPE_MANUALLY) {
                    try {
                        Mail::to($user->email)
                            ->send(new ManualPaymentGuideMail($manualPaymentGuide, $user));
                        $email = getSetting() ? getSetting()->email : null;
                        if ($email) {
                            Mail::to($email)
                                ->send(new AdminManualPaymentMail($adminMailData, $email));
                        }
                    } catch (\Exception $e) {
                        // Log the error but don't fail the subscription creation
                        \Log::error('Failed to send manual payment guide email: ' . $e->getMessage());
                    }
                }
            }

            // Send Email Razorpay, paypal Payment Success
            if (! env('DISABLE_PAYMENT_EMAILS', false)) {
                if ($paymentType != null) {
                    if ($paymentType == Subscription::TYPE_RAZORPAY || $paymentType == Subscription::TYPE_PAYPAL || $paymentType == Subscription::TYPE_STRIPE) {
                        try {
                            $successData = [
                                'name' => $user->name,
                                'planName' => $subscription->plan->name,
                            ];
                            Mail::to($user->email)->send(new SubscriptionPaymentSuccessMail($successData));
                        } catch (\Exception $e) {
                            // Log the error but don't fail the subscription creation
                            \Log::error('Failed to send payment success email: ' . $e->getMessage());
                        }
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
