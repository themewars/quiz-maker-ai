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
            \Log::info('User already verified, redirecting to login with message');
            return redirect()->route('login')->with('success', 'Your email is already verified. You can login now.');
        }

        if ($user->markEmailAsVerified()) {
            \Log::info('Email verified successfully, redirecting to login with success message');
            \Log::info('User authenticated status: ' . (auth()->check() ? 'true' : 'false'));
            \Log::info('User ID: ' . $user->id . ', Email: ' . $user->email);
            return redirect()->route('login')->with('success', 'Your email has been successfully verified! You can now login to your account.');
        }

        \Log::info('Email verification failed');
        Notification::make()
            ->danger()
            ->title(__('messages.home.your_email_verification_failed'))
            ->send();
        return redirect()->route('login');
    }
}
