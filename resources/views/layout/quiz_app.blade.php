<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ getActiveLanguage()['code'] == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ getFaviconUrl() }}" type="image/png">

    <title>{{ getAppName() }}</title>

    <link rel="preconnect" href="//fonts.bunny.net">
    <link href="//fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="{{ asset('js/bootstrap.min.js') }}" ></script>
    <script async src="https://www.google.com/recaptcha/api.js"></script>
    @vite('resources/css/app.css')
    @vite('resources/css/home.css')
    @vite('resources/assets/js/pages.js')
    @vite('resources/js/app.js')   

</head>

<body class="font-['outfit'] text-black antialiased bg-cover bg-no-repeat bg-center min-h-screen"
    style="background-image: url('{{ asset('images/bg-img.png') }}');">

    <!-- Site Header -->
    <header class="sticky-header">
        <nav class="container">
            <div class="logo">
                <a href="{{ route('home') }}">
                    <img src="{{ getAppLogo() }}" alt="{{ getAppName() }}">
                </a>
            </div>
            <div class="header-menu">
                <div id="navbar-scrollspy" class="mobile-view-nav">
                    <ul class="nav-links">
                        <li>
                            <a class="nav-link scrollspy-link"
                                href="{{ Route::currentRouteName() == 'home' ? '#features' : route('home') . '#features' }}">
                                {{ __('messages.home.features') }}
                            </a>
                        </li>
                        <li><a class="nav-link scrollspy-link"
                                href="{{ Route::currentRouteName() == 'home' ? '#about' : route('home') . '#about' }}">{{ __('messages.home.about') }}</a>
                        </li>
                        @if (getHeaderQuiz())
                            <li><a class="nav-link scrollspy-link"
                                    href="{{ Route::currentRouteName() == 'home' ? '#examples' : route('home') . '#examples' }}">Exams</a>
                            </li>
                        @endif
                        <li><a class="nav-link"
                                href="{{ route('pricing') }}">{{ __('messages.home.pricing') }}</a>
                        </li>
                        <li class="language-dropdown">
                            <a href="javascript:void(0)">{{ __('messages.home.language') }}</a>
                            <ul class="dropdown-menu">
                                @foreach (getAllLanguages() as $lang => $value)
                                    <li>
                                        <img src="{{ asset('images/language/' . $lang . '.png') }}"
                                            alt="{{ $value }}" />
                                        <a class="dropdown-item change-language" href="javascript:void(0)"
                                            data-url="{{ route('change.language', ['code' => $lang]) }}">
                                            {{ $value }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                    <div class="sign-up-button">
                        @auth
                            <a href="{{ auth()->user()->hasRole('admin') ? route('filament.admin.pages.dashboard') : route('filament.user.pages.dashboard') }}"
                                class="btn btn-primary">{{ __('messages.dashboard.dashboard') }}</a>
                        @else
                            <a href="{{ route('filament.auth.auth.register') }}"
                                class="btn btn-primary">{{ __('messages.home.sign_up_free') }}</a>
                        @endauth
                    </div>
                </div>
            </div>
            <div class="menu-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    @yield('content')

    <!-- Site Footer -->
    <footer>
        <div class="container">
            <div class="grid-container">
                <div class="grid-item">
                    <div class="footer-logo">
                        <img src="{{ getAppLogo() }}" alt="{{ getAppName() }}">
                    </div>
                    <p class="text-light-gray">
                        {{ !empty(getSetting()->footer_description) ? getSetting()->footer_description : __('messages.home.footer_description') }}
                    </p>
                </div>

                <div class="grid-item">
                    <h3>{{ __('messages.home.company') }}</h3>
                    <ul>
                        <li><a href="{{ route('home') }}">{{ __('messages.home.home') }}</a></li>
                        <li><a class="scrollspy-link" href="{{ route('home') . '#features' }}">{{ __('messages.home.features') }}</a></li>
                        <li><a class="scrollspy-link" href="{{ route('home') . '#about' }}">{{ __('messages.home.about') }}</a></li>
                        <li><a href="{{ route('pricing') }}">{{ __('messages.home.pricing') }}</a></li>
                    </ul>
                </div>

                <div class="grid-item">
                    <h3>{{ __('messages.home.legal') }}</h3>
                    <ul>
                        @if (getSetting() && getSetting()->terms)
                            <li><a href="{{ route('terms') }}">{{ __('messages.home.terms') }}</a></li>
                        @endif
                        @if (getSetting() && getSetting()->policy)
                            <li><a href="{{ route('policy') }}">{{ __('messages.home.privacy_policy') }}</a></li>
                        @endif
                        @if (getSetting() && getSetting()->cookie_policy)
                            <li><a href="{{ route('cookie') }}">{{ __('messages.home.cookie_policy') }}</a></li>
                        @endif
                        @if (getSetting() && !empty(getSetting()->custom_legal_pages))
                            @foreach (getSetting()->custom_legal_pages as $page)
                                <li><a href="{{ route('custom.legal', $page['slug']) }}">{{ $page['title'] }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                @if (getSetting() && (getSetting()->facebook_url || getSetting()->twitter_url || getSetting()->instagram_url || getSetting()->linkedin_url || getSetting()->pinterest_url))
                    <div class="grid-item">
                        <h3>{{ __('messages.home.follow_us') }}</h3>
                        <div class="social-media">
                            @if (getSetting() && getSetting()->facebook_url)
                                <a href="{{ getSetting()->facebook_url }}" target="_blank">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            @endif
                            @if (getSetting() && getSetting()->twitter_url)
                                <a href="{{ getSetting()->twitter_url }}" target="_blank">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            @endif
                            @if (getSetting() && getSetting()->instagram_url)
                                <a href="{{ getSetting()->instagram_url }}" target="_blank">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="currentColor" stroke-width="2" />
                                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" stroke="currentColor" stroke-width="2" fill="none" />
                                        <line x1="17.5" y1="6.5" x2="17.5" y2="6.5" stroke="currentColor" stroke-width="2" />
                                    </svg>
                                </a>
                            @endif
                            @if (getSetting() && getSetting()->linkedin_url)
                                <a href="{{ getSetting()->linkedin_url }}" target="_blank">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z" stroke="currentColor" stroke-width="2" />
                                        <rect x="2" y="9" width="4" height="12" stroke="currentColor" stroke-width="2" />
                                        <circle cx="4" cy="4" r="2" stroke="currentColor" stroke-width="2" />
                                    </svg>
                                </a>
                            @endif
                            @if (getSetting() && getSetting()->pinterest_url)
                                <a href="{{ getSetting()->pinterest_url }}" target="_blank">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M8 20l1-4a6 6 0 1 1 4-11 6 6 0 0 1 3 11" stroke="currentColor" stroke-width="2" fill="none" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="footer-bottom">
            <div class="copyright">
                © 2025 ExamGenerator.AI.  a YoMagic Startup ............
            </div>
            @if (getSetting() && (getSetting()->facebook_url || getSetting()->twitter_url || getSetting()->instagram_url || getSetting()->linkedin_url || getSetting()->pinterest_url))
                <div class="social-media">
                    @if (getSetting() && getSetting()->facebook_url)
                        <a href="{{ getSetting()->facebook_url }}" target="_blank">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    @endif
                    @if (getSetting() && getSetting()->twitter_url)
                        <a href="{{ getSetting()->twitter_url }}" target="_blank">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    @endif
                    @if (getSetting() && getSetting()->instagram_url)
                        <a href="{{ getSetting()->instagram_url }}" target="_blank">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="currentColor" stroke-width="2" />
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" stroke="currentColor" stroke-width="2" fill="none" />
                                <line x1="17.5" y1="6.5" x2="17.5" y2="6.5" stroke="currentColor" stroke-width="2" />
                            </svg>
                        </a>
                    @endif
                    @if (getSetting() && getSetting()->linkedin_url)
                        <a href="{{ getSetting()->linkedin_url }}" target="_blank">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z" stroke="currentColor" stroke-width="2" />
                                <rect x="2" y="9" width="4" height="12" stroke="currentColor" stroke-width="2" />
                                <circle cx="4" cy="4" r="2" stroke="currentColor" stroke-width="2" />
                            </svg>
                        </a>
                    @endif
                    @if (getSetting() && getSetting()->pinterest_url)
                        <a href="{{ getSetting()->pinterest_url }}" target="_blank">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M8 20l1-4a6 6 0 1 1 4-11 6 6 0 0 1 3 11" stroke="currentColor" stroke-width="2" fill="none" />
                            </svg>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </footer>

</body>

</html>
