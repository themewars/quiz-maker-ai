<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuestionCountWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $record = $this->getRecord();
        $currentQuestionCount = Question::where('quiz_id', $record->id ?? 0)->count();
        $subscription = getActiveSubscription();
        $maxQuestions = 0;
        
        if ($subscription && $subscription->plan) {
            if (is_numeric($subscription->plan->max_questions_per_exam)) {
                $maxQuestions = (int)$subscription->plan->max_questions_per_exam;
            } elseif (is_array($subscription->plan->max_questions_per_exam) && isset($subscription->plan->max_questions_per_exam[0]) && is_numeric($subscription->plan->max_questions_per_exam[0])) {
                $maxQuestions = (int)$subscription->plan->max_questions_per_exam[0];
            }
        }

        $remaining = $maxQuestions > 0 ? $maxQuestions - $currentQuestionCount : 0;
        $percentage = $maxQuestions > 0 ? ($currentQuestionCount / $maxQuestions) * 100 : 0;

        return [
            Stat::make('Questions in this Exam', $currentQuestionCount)
                ->description($maxQuestions > 0 ? "of {$maxQuestions} questions ({$remaining} remaining)" : 'questions')
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color($percentage > 80 ? 'warning' : 'success')
                ->chart([$currentQuestionCount]),
        ];
    }
}
