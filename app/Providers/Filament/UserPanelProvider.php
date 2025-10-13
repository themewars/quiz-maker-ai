<?php

namespace App\Providers\Filament;

use App\Enums\UserSidebar;
use App\Filament\Pages\Auth\CustomEditProfile;
use App\Http\Middleware\CheckNoOfQuiz;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\RedirectAuthenticated;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Middleware\RoleMiddleware;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->brandName(getAppName())
            ->brandLogo(fn() => view('filament.logo'))
            ->brandLogoHeight('2.5rem')
            ->favicon(getFaviconUrl())
            ->colors([
                'primary' => Color::Purple,
                'sky' => Color::Sky,
            ])
            ->breadcrumbs(false)
            ->sidebarCollapsibleOnDesktop()
            ->profile(CustomEditProfile::class, isSimple: false)
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn() => __('messages.subscription.manage_subscription'))
                    ->icon('heroicon-o-star')
                    ->url(fn() => route('filament.user.pages.manage-subscription')),
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->navigationItems([
                NavigationItem::Make('Manage Subscription')
                    ->label(fn() => __('messages.subscription.manage_subscription'))
                    ->url(fn() => route('filament.user.pages.manage-subscription'))
                    ->icon('heroicon-o-document-currency-dollar')
                    ->sort(UserSidebar::MANAGE_SUBSCRIPTION->value)
                    ->isActiveWhen(fn() => request()->routeIs('filament.user.pages.manage-subscription')),
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                CheckSubscription::class,
                CheckNoOfQuiz::class
            ])
            ->authMiddleware([
                RedirectAuthenticated::class,
                RoleMiddleware::class . ':user',
            ]);
    }

    public function register(): void
    {
        parent::register();
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::body.end',
            fn(): string => '<style>
                .fi { opacity: 1 !important; }
                .fi .fi-stats-overview, .fi .fi-wi-stats-overview-stat, .fi .fi-widget, .fi .fi-section, .fi .fi-card, .fi .fi-simple-main { opacity: 1 !important; }
                .fi .fi-heading, .fi .fi-section-header, .fi .fi-stats-overview-stat-value, .fi .fi-stats-overview-stat-description { color: #1f2937 !important; opacity: 1 !important; }
                .fi .opacity-70, .fi .opacity-60, .fi .opacity-50, .fi .opacity-40, .fi .opacity-30 { opacity: 1 !important; }
                .fi-sidebar, .fi-sidebar .fi-sidebar-item, .fi-topbar { opacity: 1 !important; }
                /* Stats overview (top faded cards) */
                .fi .fi-wi-stats-overview-stat, .fi .fi-wi-stats-overview-stat * { opacity: 1 !important; color: #111827 !important; }
                .fi .fi-wi-stats-overview-stat svg { color: #6b7280 !important; opacity: 1 !important; }
                .fi .fi-wi-stats-overview-stat .fi-card { background-color: #ffffff !important; }
            </style>'
        );

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::head.end',
            fn(): string => '<script>try{localStorage.setItem("theme","light");document.documentElement.classList.remove("dark");}catch(e){}</script>'
        );
    }

    public function changePassword(): string
    {
        return '<a class="flex items-center w-full gap-2 p-2 text-sm transition-colors duration-75 rounded-md outline-none cursor-pointer fi-dropdown-list-item whitespace-nowrap disabled:pointer-events-none disabled:opacity-70 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5 fi-dropdown-list-item-color-gray fi-color-gray" @click="$dispatch(\'open-modal\', {id: \'change-password-modal\'})">
                <svg class="w-5 h-5 text-gray-400 fi-dropdown-list-item-icon dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"></path>
                </svg>
                <span class="flex-1 text-gray-700 truncate fi-dropdown-list-item-label text-start dark:text-gray-200" style="">' . __('messages.user.change_password') . '</span>
                </a>';
    }
}
