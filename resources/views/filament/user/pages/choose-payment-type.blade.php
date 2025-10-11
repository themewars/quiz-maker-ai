<section class="flex flex-col py-8 gap-y-8">
    <header class="flex flex-col gap-4 fi-header sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight fi-header-heading text-gray-950 dark:text-white sm:text-3xl">
                {{ __('messages.common.payment') }}
            </h1>
        </div>
        <div class="flex flex-wrap items-center justify-start gap-3 fi-ac shrink-0">
            <a href="{{ route('filament.user.pages.upgrade-subscription') }}"
                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action">
                <span class="fi-btn-label">{{ __('messages.common.back') }}</span>
            </a>
        </div>
    </header>
    <div class="py-6 border border-gray-200 rounded-lg dark:border-white/10">
        <div class="flex flex-col justify-center gap-4 px-4 md:flex-row">
            @if ($currentActivePlan !== null)
                <div
                    class="w-full p-4 bg-white rounded-lg shadow-sm shadow fi-section rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="pb-4">
                        <h3 class="">
                            <span
                                class="text-2xl font-bold text-primary-400 dark:text-primary-600">{{ __('messages.subscription.current_plan') }}</span>
                        </h3>
                    </div>
                    <div class="">
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.plan.plan_name') }}</h4>
                            <span
                                class="w-1/2 text-gray-600 dark:text-gray-400">{{ $currentActivePlan['plan']['name'] }}</span>
                        </div>
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.plan.plan_amount') }}</h4>
                            <span
                                class="w-1/2 text-gray-600 dark:text-gray-400">
                                {{ getCurrencyPosition() ? $currentActivePlan['currency_icon'] . ' ' . $currentActivePlan['plan_amount'] : $currentActivePlan['plan_amount'] . ' ' . $currentActivePlan['currency_icon'] }}
                            </span>
                        </div>
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.subscription.start_date') }}</h4>
                            <span
                                class="w-1/2 text-gray-600 dark:text-gray-400">{{ date('d/m/Y', strtotime($currentActivePlan['starts_at'])) }}</span>
                        </div>
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.subscription.end_date') }}</h4>
                            <span
                                class="w-1/2 text-gray-600 dark:text-gray-400">{{ date('d/m/Y', strtotime($currentActivePlan['ends_at'])) }}</span>
                        </div>
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.subscription.used_days') }}</h4>
                            <span class="w-1/2 text-gray-600 dark:text-gray-400">{{ $currentActivePlan['used_days'] }}
                                {{ __('messages.subscription.days') }}</span>
                        </div>
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.subscription.remaining_days') }}</h4>
                            <span
                                class="w-1/2 text-gray-600 dark:text-gray-400">{{ $currentActivePlan['remaining_days'] }}
                                {{ __('messages.subscription.days') }}</span>
                        </div>
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.subscription.used_balance') }}</h4>
                            <span
                                class="w-1/2 text-gray-600 dark:text-gray-400">
                                {{ getCurrencyPosition() ? $currentActivePlan['currency_icon'] . ' ' . $currentActivePlan['used_balance'] : $currentActivePlan['used_balance'] . ' ' . $currentActivePlan['currency_icon'] }}
                            </span>
                        </div>
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.subscription.remaining_balance') }}</h4>
                            <span
                                class="w-1/2 text-gray-600 dark:text-gray-400">
                                {{ getCurrencyPosition() ? $currentActivePlan['currency_icon'] . ' ' . $currentActivePlan['remaining_balance'] : $currentActivePlan['remaining_balance'] . ' ' . $currentActivePlan['currency_icon'] }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
            <div
                class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 rounded-lg shadow p-4 w-full @if ($currentActivePlan == null) max-w-xl @endif">
                <div class="pb-4">
                    <h3 class="flex items-center gap-4">
                        <span
                            class="text-2xl font-bold text-primary-400 dark:text-primary-600">{{ __('messages.plan.new_plan') }}</span>
                        {{-- @if ($plan->trial_days > 0)
                            <span
                                style="--c-50:var(--warning-50);--c-400:var(--warning-400);--c-600:var(--warning-600);"
                                class="fi-badge rounded-lg ring-1 ring-inset px-4 py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-warning">{{ __('messages.subscription.trial_plan') }}</span>
                        @endif --}}
                    </h3>
                </div>
                <div class="">
                    <div class="flex items-center py-2">
                        <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                            {{ __('messages.plan.plan_name') }}</h4>
                        <span class="w-1/2 text-gray-600 dark:text-gray-400">{{ $plan->name }}</span>
                    </div>
                    <div class="flex items-center py-2">
                        <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                            {{ __('messages.plan.plan_amount') }}</h4>
                        <span class="w-1/2 text-gray-600 dark:text-gray-400">
                            {{ getCurrencyPosition() ? $plan->currency_icon . ' ' . $plan->price : $plan->price . ' ' . $plan->currency_icon }}
                        </span>
                    </div>
                    <div class="flex items-center py-2">
                        <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                            {{ __('messages.subscription.start_date') }}</h4>
                        <span
                            class="w-1/2 text-gray-600 dark:text-gray-400">{{ date('d/m/Y', strtotime($plan->start_date)) }}</span>
                    </div>
                    <div class="flex items-center py-2">
                        <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                            {{ __('messages.subscription.end_date') }}</h4>
                        <span
                            class="w-1/2 text-gray-600 dark:text-gray-400">{{ date('d/m/Y', strtotime($plan->end_date)) }}</span>
                    </div>
                    <div class="flex items-center py-2">
                        <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                            {{ __('messages.subscription.total_days') }}</h4>
                        <span class="w-1/2 text-gray-600 dark:text-gray-400">{{ $plan->total_days }}
                            {{ __('messages.subscription.days') }}</span>
                    </div>
                    @if ($currentActivePlan !== null)
                        <div class="flex items-center py-2">
                            <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                                {{ __('messages.subscription.remaining_balance_of_prev_plan') }}</h4>
                            <span
                                class="w-1/2 text-gray-600 dark:text-gray-400">
                                {{ getCurrencyPosition() ? $currentActivePlan['currency_icon'] . ' ' . $currentActivePlan['remaining_balance'] : $currentActivePlan['remaining_balance'] . ' ' . $currentActivePlan['currency_icon'] }}
                            </span>
                        </div>
                    @endif
                    <div class="flex items-center py-2">
                        <h4 class="w-1/2 font-bold text-gray-600 dark:text-gray-200">
                            {{ __('messages.subscription.payable_amount') }}</h4>
                        <span class="w-1/2 text-gray-600 dark:text-gray-400">
                            {{ getCurrencyPosition() ? $plan->currency_icon . ' ' . $plan->payable_amount : $plan->payable_amount . ' ' . $plan->currency_icon }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center">
            <div class="w-full max-w-sm pt-6">
                {{-- Payable Amount 0 --}}
                @if ($paymentAmount <= 0)
                    <div class="text-center">
                        <x-filament-panels::form wire:submit="save">
                            <div>
                                <x-filament::button wire:loading.attr="disabled" type="submit" class="px-4">
                                    <span wire:loading.remove>{{ __('messages.subscription.pay_switch_plan') }}</span>
                                    <span wire:loading>
                                        <span class="flex justify-center">
                                            <svg aria-hidden="true" role="status"
                                                class="inline w-4 h-4 my-auto text-white me-1 animate-spin"
                                                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                    fill="#E5E7EB" />
                                                <path
                                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                    fill="currentColor" />
                                            </svg>
                                            <span class="ms-1">
                                                {{ __('messages.subscription.pay_switch_plan') }}
                                            </span>
                                        </span>
                                    </span>
                                </x-filament::button>
                            </div>
                        </x-filament-panels::form>
                @endif

                <div class="">
                    @if ($paymentAmount > 0)
                        <!-- Payment Type Selection Cards -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">
                                {{ __('messages.plan.select_payment_type') }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                @php
                                    $paymentTypes = \App\Models\Subscription::getPaymentType();
                                @endphp
                                
                                @foreach($paymentTypes as $typeId => $typeName)
                                    <div class="payment-card relative cursor-pointer border-2 rounded-lg p-4 transition-all duration-200 hover:shadow-lg {{ $paymentType == $typeId ? 'selected border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-primary-300' }}"
                                         wire:click="$set('paymentType', {{ $typeId }})"
                                         onclick="document.querySelector('select[name=\"data.payment_type\"]').value = '{{ $typeId }}'; document.querySelector('select[name=\"data.payment_type\"]').dispatchEvent(new Event('change'));">
                                        
                                        <!-- Selection Indicator -->
                                        <div class="absolute top-2 right-2">
                                            <div class="selection-indicator w-5 h-5 rounded-full border-2 flex items-center justify-center {{ $paymentType == $typeId ? 'border-primary-500 bg-primary-500' : 'border-gray-300 dark:border-gray-600' }}">
                                                @if($paymentType == $typeId)
                                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Payment Type Icon -->
                                        <div class="text-center mb-3">
                                            @if($typeId == 1) {{-- RazorPay --}}
                                                <div class="payment-icon w-12 h-12 mx-auto bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                    </svg>
                                                </div>
                                            @elseif($typeId == 2) {{-- PayPal --}}
                                                <div class="payment-icon w-12 h-12 mx-auto bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.432-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.105-.722c-1.125-5.08-5.24-7.62-10.48-7.62H3.28a.641.641 0 0 0-.633.74l2.47 19.696c.062.358.37.64.733.64h4.606c.524 0 .968-.382 1.05-.9l1.12-7.106h2.19c4.298 0 7.664-1.747 8.647-6.797.03-.144.054-.289.077-.432.292-1.867-.002-3.137-1.012-4.287z"/>
                                                    </svg>
                                                </div>
                                            @elseif($typeId == 3) {{-- Stripe --}}
                                                <div class="payment-icon w-12 h-12 mx-auto bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.274 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                                                    </svg>
                                                </div>
                                            @elseif($typeId == 4) {{-- Manual --}}
                                                <div class="payment-icon w-12 h-12 mx-auto bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Payment Type Name -->
                                        <div class="text-center">
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $typeName }}</h4>
                                            @if($typeId == 4)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Admin approval required</p>
                                            @else
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Instant payment</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Hidden Form for Livewire -->
                        <div style="display: none;">
                            {{ $this->form }}
                        </div>
                    @endif

                    {{-- Manually Payment --}}
                    @if ($paymentType == 4)
                        <div class="pt-4 text-center">
                            <x-filament-panels::form wire:submit="save">
                                <div>
                                    <x-filament::button wire:loading.attr="disabled" type="submit" class="px-4">
                                        <span class="flex justify-center">
                                            <svg wire:loading aria-hidden="true" role="status"
                                                class="hidden inline w-4 h-4 my-auto text-white me-1 animate-spin"
                                                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                    fill="#E5E7EB" />
                                                <path
                                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                    fill="currentColor" />
                                            </svg>
                                            <span class="ms-1">
                                                {{ __('messages.subscription.pay_switch_plan') }}
                                            </span>
                                        </span>
                                    </x-filament::button>
                                </div>
                            </x-filament-panels::form>
                        </div>
                        {{-- Razorpay Payment --}}
                    @elseif ($paymentType == 1)
                        <input type="hidden" id="planInput" name="plan" value='@json($plan)'>
                        <div class="pt-4 flex justify-center">
                            <x-filament::button wire:loading.attr="disabled" type="submit" class="px-4"
                                id="razorpayPayment">
                                <span class="flex justify-center">
                                    <svg wire:loading aria-hidden="true" role="status"
                                        class="hidden inline w-4 h-4 my-auto text-white me-1 animate-spin"
                                        viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                            fill="#E5E7EB" />
                                        <path
                                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                            fill="currentColor" />
                                    </svg>
                                    <span class="ms-1">
                                        {{ __('messages.subscription.pay_switch_plan') }}
                                    </span>
                                </span>
                            </x-filament::button>
                        </div>
                        {{-- Paypal Payment --}}
                    @elseif ($paymentType == 2)
                        <form action="{{ route('paypal.purchase') }}" method="POST" class="flex justify-center">
                            @csrf
                            <input type="hidden" name="plan" value='@json($plan)'>
                            <div class="pt-4">
                                <x-filament::button wire:loading.attr="disabled" type="submit" class="px-4">
                                    <span class="flex justify-center">
                                        <svg wire:loading aria-hidden="true" role="status"
                                            class="hidden inline w-4 h-4 my-auto text-white me-1 animate-spin"
                                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                fill="#E5E7EB" />
                                            <path
                                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                fill="currentColor" />
                                        </svg>
                                        <span class="ms-1">
                                            {{ __('messages.subscription.pay_switch_plan') }}
                                        </span>
                                    </span>
                                </x-filament::button>
                            </div>
                        </form>
                        {{-- Stripe Payment --}}
                    @elseif ($paymentType == 3)
                        <form action="{{ route('stripe.purchase') }}" method="POST" class="flex justify-center">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $plan }}">
                            <div class="pt-4">
                                <x-filament::button wire:loading.attr="disabled" type="submit" class="px-4">
                                    <span class="flex justify-center">
                                        <svg wire:loading aria-hidden="true" role="status"
                                            class="hidden inline w-4 h-4 my-auto text-white me-1 animate-spin"
                                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                fill="#E5E7EB" />
                                            <path
                                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                fill="currentColor" />
                                        </svg>
                                        <span class="ms-1">
                                            {{ __('messages.subscription.pay_switch_plan') }}
                                        </span>
                                    </span>
                                </x-filament::button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        {{-- @if ($paymentType == 4)
            <div class="w-full px-6 pt-6 text-left">
                {!! $manualPaymentGuide !!}
            </div>
        @endif --}}
    </div>
</section>

@push('styles')
<style>
    .payment-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .payment-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .payment-card.selected {
        border-color: #6366f1 !important;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    }
    
    .dark .payment-card.selected {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    }
    
    .payment-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(99, 102, 241, 0.05) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .payment-card:hover::before {
        opacity: 1;
    }
    
    .payment-icon {
        transition: transform 0.3s ease;
    }
    
    .payment-card:hover .payment-icon {
        transform: scale(1.1);
    }
    
    .selection-indicator {
        transition: all 0.3s ease;
    }
    
    .payment-card.selected .selection-indicator {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .payment-card {
            padding: 1rem;
        }
        
        .payment-icon {
            width: 2.5rem;
            height: 2.5rem;
        }
    }
</style>
@endpush

@push('scripts')
@vite('resources/js/razorpay-checkout.js')
<script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
<script>
    $(document).ready(function() {
        window.listenClick = function(selector, callback) {
            $(document).on('click', selector, callback)
        }

        listenClick('#razorpayPayment', function(e) {
            e.preventDefault();
            let planInput = $('#planInput').val();
            let plan = JSON.parse(planInput);

            $.ajax({
                url: "{{ route('razorpay.purchase') }}",
                type: "POST",
                dataType: "json",
                contentType: 'application/json',
                data: JSON.stringify({
                    plan: plan
                }),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                success: function(result) {
                    const options = {
                        key: "{{ getPaymentSetting()->razorpay_key }}",
                        amount: result.data.payable_amount * 100,
                        currency: result.data.currency,
                        name: "{{ $plan->name }}",
                        description: 'Purchase Plan',
                        order_id: result.data.order_id,
                        handler: function(response) {
                            $.ajax({
                                url: "{{ route('razorpay.success') }}",
                                type: "POST",
                                dataType: "json",
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    razorpay_payment_id: response
                                        .razorpay_payment_id,
                                    razorpay_order_id: response
                                        .razorpay_order_id,
                                    razorpay_signature: response
                                        .razorpay_signature
                                }),
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]')
                                        .getAttribute('content')
                                },
                                success: function(result) {
                                    new FilamentNotification()
                                        .title(result.message)
                                        .success()
                                        .send();
                                    setTimeout(function() {
                                        window.location.href =
                                            result.redirect;
                                    }, 1000);
                                }
                            });
                        },
                        theme: {
                            color: '#4637d8'
                        },
                        'modal': {
                            'ondismiss': function() {
                                redirect = "{{ route('razorpay.failed') }}";
                                window.location.href = redirect;
                            },
                        }
                    };
                    const rzp = new Razorpay(options);
                    rzp.open();
                }
            });
        });
    });
</script>
@endpush
