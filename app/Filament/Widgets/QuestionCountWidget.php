<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use Filament\Widgets\Widget;

class QuestionCountWidget extends Widget
{
    protected static string $view = 'filament.widgets.question-count-widget';

    protected int | string | array $columnSpan = 'full';

    public ?int $quizId = null;

    public function mount(): void
    {
        // Get quiz ID from the current page
        $this->quizId = request()->route('record');
    }

    public function getViewData(): array
    {
        $currentQuestionCount = Question::where('quiz_id', $this->quizId ?? 0)->count();
        $subscription = getActiveSubscription();
        $maxQuestions = 0;
        
        if ($subscription && $subscription->plan) {
            if (is_numeric($subscription->plan->max_questions_per_exam)) {
                $maxQuestions = (int)$subscription->plan->max_questions_per_exam;
            } elseif (is_array($subscription->plan->max_questions_per_exam) && isset($subscription->plan->max_questions_per_exam[0]) && is_numeric($subscription->plan->max_questions_per_exam[0])) {
                $maxQuestions = (int)$subscription->plan->max_questions_per_exam[0];
            }
        }

        return [
            'currentQuestionCount' => $currentQuestionCount,
            'maxQuestions' => $maxQuestions,
        ];
    }
}
