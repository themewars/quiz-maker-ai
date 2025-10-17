<?php

namespace App\Http\Controllers;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        // Debug: Log the verification attempt
        \Log::info('VerifyEmailController called with ID: ' . $request->route('id'));
        \Log::info('Request URL: ' . $request->fullUrl());
        
        $user = User::findOrFail($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            \Log::info('User already verified, redirecting to user dashboard');
            Notification::make()
                ->success()
                ->title(__('messages.home.email_already_verified'))
                ->send();
            return redirect()->route('filament.user.pages.dashboard');
        }

        if ($user->markEmailAsVerified()) {
            \Log::info('Email verified successfully, redirecting to user dashboard');
            Notification::make()
                ->success()
                ->title(__('messages.home.your_email_verified_success'))
                ->send();
            return redirect()->route('filament.user.pages.dashboard');
        }

        \Log::info('Email verification failed');
        Notification::make()
            ->danger()
            ->title(__('messages.home.your_email_verification_failed'))
            ->send();
        return redirect()->route('login');
    }
}
