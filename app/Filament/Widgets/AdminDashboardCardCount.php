<?php

namespace App\Filament\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Quiz;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserQuiz;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Number;


class AdminDashboardCardCount extends BaseWidget
{
    protected static string $view = 'filament.widgets.admin-dashboard-state';

    protected function getViewData(): array
    {

        $userQuery = User::whereNot('id', auth()->id());
        $totalUser = $userQuery->count();
        $activeUsers = $userQuery->where('status', 1)->count();


        // Count only users with an active PAID subscription (payable_amount > 0), distinct by user
        $paidUser = Subscription::where('status', SubscriptionStatus::ACTIVE)
            ->where('payable_amount', '>', 0)
            ->distinct('user_id')
            ->count('user_id');
        $payableAmount = Subscription::sum('payable_amount');


        $totalQuiz = Quiz::get()->count();
        $activeQuiz = Quiz::where('status', 1)->count();
        $participant = UserQuiz::count();
        $completedQuizCount = UserQuiz::whereNotNull('completed_at')->count();
        $completedQuiz = $participant > 0 ? round(($completedQuizCount / $participant) * 100, 2) : 0;


        return [
            'totalUser' => $totalUser,
            'activeUsers' => $activeUsers,
            'paidUser' => $paidUser,
            'payableAmount' => $payableAmount,
            'activeQuiz' => $activeQuiz,
            'totalQuiz' => $totalQuiz,
            'activeExam' => $activeQuiz,
            'totalExam' => $totalQuiz,
            'participant' => $participant,
            'completedQuiz' =>  $completedQuiz,
            'completedExam' =>  $completedQuiz,
        ];
    }
}
