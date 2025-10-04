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

    <!-- Pricing Plans Section -->
    <section class="pricing-plans">
        <div class="container">
            <div class="pricing-grid">
                @foreach($plans as $plan)
                    <div class="pricing-card animate-fade-in {{ $loop->index == 1 ? 'popular' : '' }}">
                        @if($loop->index == 1)
                            <span class="popular-badge">{{ __('messages.home.popular') }}</span>
                        @endif
                        
                        <div class="pricing-header">
                            <h3>{{ $plan->name }}</h3>
                            @if(getCurrencyPosition())
                                <div class="price">{{ $plan->currency->symbol ?? '₹' }}
                                    {{ $plan->price ?? 0 }} /
                                    <span class="frequency">{{ __(\App\Enums\PlanFrequency::from($plan->frequency)->getLabel()) }}</span>
                                </div>
                            @else
                                <div class="price">
                                    {{ $plan->price ?? 0 }} {{ $plan->currency->symbol ?? '₹' }}
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
                <div class="faq-item">
                    <div class="faq-question" data-accordion="faq-1">
                        <h3>What is QuizWhiz AI?</h3>
                        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="faq-answer" id="faq-1">
                        <p>QuizWhiz AI is an online tool that allows you to create exams, quizzes, and assessments instantly using artificial intelligence. You can generate tests from PDF, text, PowerPoint, or even YouTube videos.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" data-accordion="faq-2">
                        <h3>Is QuizWhiz AI free to use?</h3>
                        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="faq-answer" id="faq-2">
                        <p>Yes! QuizWhiz AI offers a free plan with basic features. For advanced options like unlimited exams, question banks, and PDF downloads, you can upgrade to a premium plan.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" data-accordion="faq-3">
                        <h3>Can I generate exams from a PDF or document?</h3>
                        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="faq-answer" id="faq-3">
                        <p>Absolutely. Simply upload your PDF, Word, or text file and QuizWhiz AI will automatically create a set of questions and answers based on the content.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" data-accordion="faq-4">
                        <h3>How accurate are AI-generated questions?</h3>
                        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="faq-answer" id="faq-4">
                        <p>While AI generates high-quality and relevant questions, we recommend reviewing the questions before finalizing to ensure they match your teaching or learning goals.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
