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
        
        // Check if user can access the current panel
        if ($user instanceof FilamentUser) {
            if ($user->canAccessPanel($panel)) {
                \Log::info('RedirectAuthenticated - User can access panel, allowing');
                return;
            } else {
                \Log::info('RedirectAuthenticated - User cannot access panel, denying');
                abort(403, 'You do not have permission to access this panel.');
            }
        }
        
        // For non-FilamentUser, deny access unless in local environment
        if (config('app.env') !== 'local') {
            \Log::info('RedirectAuthenticated - Non-FilamentUser denied access in production');
            abort(403, 'Access denied.');
        }
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
