<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class QuestionCountWidget extends Widget
{
    protected static string $view = 'filament.widgets.question-count-widget';

    protected int | string | array $columnSpan = 'full';

    public ?int $quizId = null;

    public static function canView(): bool
    {
        $panel = Filament::getCurrentPanel();
        return $panel && $panel->getId() === 'user';
    }

    public function mount($record = null): void
    {
        // Get quiz ID from the mount parameter first
        if ($record && isset($record->id)) {
            $this->quizId = $record->id;
            return;
        }
        
        // Get quiz ID from the current page - try multiple ways
        $this->quizId = request()->route('record') ?? request()->route('id') ?? request()->get('id');
        
        // If still null, try to get from the current page context
        if (!$this->quizId) {
            $segments = request()->segments();
            foreach ($segments as $segment) {
                if (is_numeric($segment)) {
                    $this->quizId = (int)$segment;
                    break;
                }
            }
        }
    }

    public function getViewData(): array
    {
        // If quizId is still null, try to get it from the current request
        if (!$this->quizId) {
            $this->quizId = request()->route('record') ?? request()->route('id') ?? request()->get('id');
            
            if (!$this->quizId) {
                $segments = request()->segments();
                foreach ($segments as $segment) {
                    if (is_numeric($segment)) {
                        $this->quizId = (int)$segment;
                        break;
                    }
                }
            }
        }

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
