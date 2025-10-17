<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Check if the request is for admin panel
        if (str_starts_with($request->path(), 'admin')) {
            return route('filament.admin.auth.login');
        }

        // Default to user panel login
        return route('filament.user.auth.login');
    }
}
