<?php

namespace App\Filament\User\Resources\QuizzesResource\Pages;

use App\Filament\User\Resources\QuizzesResource;
use App\Enums\SubscriptionStatus;
use App\Models\Quiz;
use App\Models\Subscription;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Htmlable;

class ListQuizzes extends ListRecords
{
    protected static string $resource = QuizzesResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [Actions\CreateAction::make()];

        // If user has reached plan limit, show upgrade notice
        $sub = Subscription::with('plan')
            ->where('user_id', Auth::id())
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->first();
        if ($sub && $sub->plan) {
            $quizCount = Quiz::where('user_id', Auth::id())
                ->whereBetween('created_at', [$sub->starts_at, $sub->ends_at])
                ->count();
            if ($quizCount >= $sub->plan->no_of_quiz) {
                Notification::make()
                    ->title(__('Your plan limit is reached. Please upgrade to create more quizzes.'))
                    ->warning()
                    ->body(__('Go to Upgrade Plan page to select a higher plan.'))
                    ->persistent()
                    ->send();
            }
        }

        return $actions;
    }
}
