<?php

namespace App\Http\Responses;

use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Filament\Notifications\Notification;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user) {
            // Refresh user to get latest role data
            $user->refresh();
            $role = $user->roles()->first();
            
            // Debug logging
            \Log::info('LoginResponse - User ID: ' . $user->id . ', Email: ' . $user->email);
            \Log::info('LoginResponse - Role: ' . ($role ? $role->name : 'null'));
            \Log::info('LoginResponse - Has admin role: ' . ($user->hasRole('admin') ? 'true' : 'false'));
            \Log::info('LoginResponse - Current URL: ' . $request->url());
            \Log::info('LoginResponse - Current route: ' . $request->route()->getName());

            // Force admin users to admin panel regardless of login location
            if ($user->hasRole('admin')) {
                \Log::info('LoginResponse - Admin user detected, forcing redirect to admin dashboard');
                return redirect()->route('filament.admin.pages.dashboard');
            }

            if ($role && $role->name === User::USER_ROLE) {
                \Log::info('LoginResponse - Redirecting to user dashboard');
                return redirect('/user');
            }
        }

        \Log::info('LoginResponse - Redirecting to home (fallback)');
        return redirect()->route('home');
    }
}
