@extends('layout.app')

@section('title', 'Pricing Plans - QuizWhiz AI')

@section('content')
<main>
    <!-- Pricing Hero Section -->
    <section class="pricing-hero">
        <div class="container">
            <div class="pricing-hero-content">
                <div class="badge badge-primary-light">{{ __('messages.home.pricing') }}</div>
                <h1>{{ __('messages.home.pricing_title') }}</h1>
                <p class="text-light-gray">{{ __('messages.home.pricing_description') }}</p>
            </div>
        </div>
    </section>

    <!-- Currency Switcher Section -->
    <section class="currency-switcher-section">
        <div class="container">
            <div class="currency-switcher">
                <label for="currency-select">{{ __('messages.currency.select_currency') }}:</label>
                <select id="currency-select" class="currency-dropdown">
                    @foreach($allCurrencies as $currency)
                        <option value="{{ $currency->code }}" 
                                data-symbol="{{ $currency->symbol }}"
                                {{ $currentCurrency->code === $currency->code ? 'selected' : '' }}>
                            {{ $currency->symbol }} {{ $currency->code }} - {{ $currency->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <!-- Pricing Plans Section -->
    <section class="pricing-plans">
        <div class="container">
            <div class="pricing-grid" id="pricing-grid">
                @foreach($plans as $plan)
                    <div class="pricing-card animate-fade-in {{ $loop->index == 1 ? 'popular' : '' }}">
                        @if($loop->index == 1)
                            <span class="popular-badge">{{ __('messages.home.popular') }}</span>
                        @endif
                        
                        <div class="pricing-header">
                            <h3>{{ $plan->name }}</h3>
                            @if(getCurrencyPosition())
                                <div class="price" data-plan-id="{{ $plan->id }}"><span class="currency">{{ $currentCurrency->symbol }}</span>
                                    <span class="amount">{{ number_format($plan->current_price, 2) }}</span> /
                                    <span class="frequency">{{ __(\App\Enums\PlanFrequency::from($plan->frequency)->getLabel()) }}</span>
                                </div>
                            @else
                                <div class="price" data-plan-id="{{ $plan->id }}">
                                    <span class="amount">{{ number_format($plan->current_price, 2) }}</span> <span class="currency">{{ $currentCurrency->symbol }}</span>
                                    <span class="frequency">{{ __(\App\Enums\PlanFrequency::from($plan->frequency)->getLabel()) }}</span>
                                </div>
                            @endif
                            <p>{{ $plan->description }}</p>
                        </div>

                        <div class="pricing-divider"></div>
                        
                        <ul class="pricing-features">
                            <li class="feature-item">
                                <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Max exams: {{ (int)($plan->no_of_quiz ?? 0) > 0 ? (int)$plan->no_of_quiz : 'Unlimited' }}</span>
                            </li>
                            <li class="feature-item">
                                <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Max questions per exam: {{ (int)($plan->max_questions_per_exam ?? 0) > 0 ? (int)$plan->max_questions_per_exam : 'Unlimited' }}</span>
                            </li>
                            <li class="feature-item">
                                <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Monthly question limit: {{ (int)($plan->max_questions_per_month ?? -1) >= 0 ? (int)$plan->max_questions_per_month : 'Unlimited' }}</span>
                            </li>
                            <li class="feature-item">
                                <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Max PDF pages: {{ (int)($plan->max_pdf_pages ?? 0) > 0 ? (int)$plan->max_pdf_pages : 'Unlimited' }}</span>
                            </li>
                            <li class="feature-item">
                                <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>PDF export: {{ $plan->export_pdf ? 'Enabled' : 'Disabled' }}</span>
                            </li>
                            <li class="feature-item">
                                <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Word export: {{ $plan->export_word ? 'Enabled' : 'Disabled' }}</span>
                            </li>
                            @if($loop->index == 0)
                                <li class="feature-item disabled">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Generate exams from PDFs/URLs</span>
                                </li>
                                <li class="feature-item disabled">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Display leaderboard</span>
                                </li>
                                <li class="feature-item disabled">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Share results with participants</span>
                                </li>
                                <li class="feature-item disabled">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Email participants</span>
                                </li>
                            @elseif($loop->index == 1)
                                <li class="feature-item">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Generate exams from PDFs/URLs</span>
                                </li>
                                <li class="feature-item">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Display leaderboard</span>
                                </li>
                                <li class="feature-item disabled">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Share results with participants</span>
                                </li>
                                <li class="feature-item disabled">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Email participants</span>
                                </li>
                            @else
                                <li class="feature-item">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Generate exams from PDFs/URLs</span>
                                </li>
                                <li class="feature-item">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Display leaderboard</span>
                                </li>
                                <li class="feature-item">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Share results with participants</span>
                                </li>
                                <li class="feature-item">
                                    <svg class="feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Email participants</span>
                                </li>
                            @endif
                        </ul>

                        @auth
                            @if(getActiveSubscription() && getActiveSubscription()->plan_id == $plan->id)
                                <button class="btn btn-outline btn-bg-white popular full-width">
                                    {{ __('messages.subscription.currently_active') }}
                                </button>
                            @else
                                @role('user')
                                    <a class="btn btn-outline btn-bg-white popular full-width"
                                        href="{{ route('filament.user.pages.choose-payment-type', ['plan' => $plan['id']]) }}">
                                        {{ __('messages.subscription.choose_plan') }}
                                    </a>
                                @endrole
                            @endif
                        @else
                            <a class="btn btn-outline btn-bg-white popular full-width"
                                href="{{ route('filament.auth.auth.register') }}">
                                {{ $plan->price == 0 ? __('messages.home.sign_up_free') : __('messages.home.sign_up_free') }}
                            </a>
                        @endauth
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="pricing-faq">
        <div class="container">
            <div class="section-header">
                <div class="badge badge-primary-light">{{ __('messages.home.faq') }}</div>
                <h2>{{ __('messages.home.faq_title') }}</h2>
                <p class="text-light-gray">{{ __('messages.home.faq_description') }}</p>
            </div>

            <div class="faq-container">
                @foreach($faqs as $faq)
                    <div class="faq-item">
                        <div class="faq-question" data-accordion="faq-{{ $loop->index }}">
                            <h3>{{ $faq->question }}</h3>
                            <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div class="faq-answer" id="faq-{{ $loop->index }}">
                            <p>{{ $faq->answer }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</main>

<!-- Currency Switcher JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const currencyBefore = {{ getCurrencyPosition() ? 'true' : 'false' }};
    const currencySelect = document.getElementById('currency-select');
    const pricingGrid = document.getElementById('pricing-grid');
    
    // Store plan data for currency switching (prebuilt in controller)
    const planData = @json($planData);
    
    currencySelect.addEventListener('change', function() {
        const selectedCurrency = this.value;
        const selectedOption = this.options[this.selectedIndex];
        const currencySymbol = selectedOption.getAttribute('data-symbol');
        
        // Show loading state
        pricingGrid.style.opacity = '0.5';
        
        // Switch currency via AJAX
        fetch('{{ route("currency.switch") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                currency: selectedCurrency
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update pricing display
                updatePricingDisplay(selectedCurrency, currencySymbol);
                
                // Reload page to update all prices
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                alert('Failed to switch currency. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to switch currency. Please try again.');
        })
        .finally(() => {
            pricingGrid.style.opacity = '1';
        });
    });
    
    function updatePricingDisplay(currencyCode, currencySymbol) {
        // Update all price displays
        const priceElements = document.querySelectorAll('.price');
        
        priceElements.forEach(priceElement => {
            const planId = parseInt(priceElement.getAttribute('data-plan-id'));
            const plan = planData.find(p => p.id === planId);
            
            if (plan) {
                const currencyPrice = plan.prices.find(p => p.currency_code === currencyCode);
                if (currencyPrice) {
                    const amountElement = priceElement.querySelector('.amount');
                    if (amountElement) {
                        amountElement.textContent = parseFloat(currencyPrice.price).toFixed(2);
                    }
                    
                    // Update currency symbol
                    const currencySpan = priceElement.querySelector('.currency');
                    if (currencySpan) {
                        currencySpan.textContent = currencySymbol;
                    }
                }
            }
        });
    }
});
</script>

<style>
.currency-switcher-section {
    padding: 2rem 0;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.currency-switcher {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.currency-switcher label {
    font-weight: 600;
    color: #333;
    margin: 0;
}

.currency-dropdown {
    padding: 0.5rem 1rem;
    border: 2px solid #007bff;
    border-radius: 8px;
    background: white;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.currency-dropdown:hover {
    border-color: #0056b3;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
}

.currency-dropdown:focus {
    outline: none;
    border-color: #0056b3;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.pricing-grid {
    transition: opacity 0.3s ease;
}
</style>
@endsection
