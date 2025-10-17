<?php

namespace App\Http\Responses;

use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;

class RegistrationResponse implements RegistrationResponseContract
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

        // Debug: Log the user and role
        \Log::info('RegistrationResponse - User ID: ' . ($user ? $user->id : 'null'));
        
        if ($user) {
            // Check if email is verified first
            if (!$user->hasVerifiedEmail()) {
                \Log::info('User email not verified, redirecting to verification page');
                return redirect()->route('verification.notice')->with('status', 'Please verify your email address to continue.');
            }

            // Refresh user to get latest role data
            $user->refresh();
            $role = $user->roles()->first();
            
            \Log::info('RegistrationResponse - Role: ' . ($role ? $role->name : 'null'));

            if ($role && $role->name === User::ADMIN_ROLE) {
                \Log::info('Redirecting to admin dashboard');
                return redirect()->route('filament.admin.pages.dashboard');
            }

            if ($role && $role->name === User::USER_ROLE) {
                \Log::info('Redirecting to user dashboard');
                return redirect()->route('filament.user.pages.dashboard');
            }
        }

        \Log::info('Redirecting to home (fallback)');
        return redirect()->route('home');
    }
}
