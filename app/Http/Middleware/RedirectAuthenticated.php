<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as Middleware;
use Filament\Models\Contracts\FilamentUser;

class RedirectAuthenticated extends Middleware
{
    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentPanel();

        // Debug logging
        \Log::info('RedirectAuthenticated - User ID: ' . $user->id . ', Email: ' . $user->email);
        \Log::info('RedirectAuthenticated - Panel ID: ' . ($panel ? $panel->getId() : 'null'));
        \Log::info('RedirectAuthenticated - User roles: ' . $user->roles->pluck('name')->implode(', '));
        \Log::info('RedirectAuthenticated - Can access panel: ' . ($user->canAccessPanel($panel) ? 'true' : 'false'));
        \Log::info('RedirectAuthenticated - Is FilamentUser: ' . ($user instanceof FilamentUser ? 'true' : 'false'));
        \Log::info('RedirectAuthenticated - App env: ' . config('app.env'));
        
        // Temporarily allow all authenticated users to access any panel
        if ($user instanceof FilamentUser) {
            \Log::info('RedirectAuthenticated - Allowing access for authenticated FilamentUser');
            return $next($request);
        }
        
        abort_if(
            $user instanceof FilamentUser ?
                (! $user->canAccessPanel($panel)) : (config('app.env') !== 'local'),
            403,
        );
    }

    protected function redirectTo($request): ?string
    {
        $panel = Filament::getCurrentPanel();
        
        if ($panel && $panel->getId() === 'admin') {
            return '/admin/login';
        }
        
        return '/login';
    }
}
