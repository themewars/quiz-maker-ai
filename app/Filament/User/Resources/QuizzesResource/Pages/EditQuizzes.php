<?php

namespace App\Filament\User\Resources\QuizzesResource\Pages;

use App\Models\Quiz;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use App\Jobs\GenerateAdditionalQuestions;
use Illuminate\Support\Facades\Cache;
use fivefilters\Readability\Readability;
use fivefilters\Readability\Configuration;
use App\Filament\User\Resources\QuizzesResource;

class EditQuizzes extends EditRecord
{
    protected static string $resource = QuizzesResource::class;

    public static $tab = Quiz::TEXT_TYPE;

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\QuestionCountWidget::class,
        ];
    }

    public function currentActiveTab()
    {
        $pre = URL::previous();
        parse_str(parse_url($pre)['query'] ?? '', $queryParams);
        $tab = $queryParams['tab'] ?? null;
        $tabType = [
            '-subject-tab' => Quiz::SUBJECT_TYPE,
            '-text-tab' => Quiz::TEXT_TYPE,
            '-url-tab' => Quiz::URL_TYPE,
            '-upload-tab' => Quiz::UPLOAD_TYPE,
        ];

        $tabType[$tab] ?? Quiz::TEXT_TYPE;
    }

    // protected function afterValidate(): void
    // {
    //     $data = $this->form->getState();

    //     if (empty($this->data['file_upload']) && empty($data['quiz_description_text']) && empty($data['quiz_description_sub']) && empty($data['quiz_description_url'])) {
    //         Notification::make()
    //             ->danger()
    //             ->title(__('messages.quiz.quiz_description_required'))
    //             ->send();
    //         $this->halt();
    //     }
    // }


    public function fillForm(): void
    {
        // Always refresh and eager-load questions+answers so first visit after create shows data
        if ($this->record) {
            $this->record->refresh()->load(['questions.answers']);
        }
        
        $quizQuestions = Session::get('quizQuestions');
        $editedBaseData = Session::get('editedQuizDataForRegeneration');
        
        // Clear session data AFTER we've retrieved it
        Session::forget('editedQuizDataForRegeneration');
        Session::forget('quizQuestions');

        $quizData = trim($quizQuestions);
        if (stripos($quizData, '```json') === 0) {
            $quizData = preg_replace('/^```json\s*|\s*```$/', '', $quizData);
            $quizData = trim($quizData);
        }

        $questionData = json_decode($quizData, true);

        if ($editedBaseData) {
            $data = $editedBaseData;

            unset($data['questions'], $data['custom_questions']);
        } else {
            $data = $this->record->attributesToArray();
            $data = $this->mutateFormDataBeforeFill($data);
        }

        // Always prioritize DB questions over session data for reliability
        if (isset($data['id'])) {
            $dbQuestions = Question::where('quiz_id', $data['id'])->with('answers')->orderBy('id')->get();
            if ($dbQuestions->count() > 0) {
                $data['questions'] = [];
                foreach ($dbQuestions as $question) {
                    $answersOption = $question->answers->map(function ($answer) {
                        return [
                            'title' => $answer->title,
                            'is_correct' => $answer->is_correct,
                        ];
                    })->toArray();
                    $correctAnswer = array_keys(array_filter(array_column($answersOption, 'is_correct')));
                    $data['questions'][] = [
                        'title' => $question->title,
                        'answers' => $answersOption,
                        'is_correct' => $correctAnswer,
                        'question_id' => $question->id,
                    ];
                }
            }
        }

        if (is_array($questionData) && !empty($questionData)) {
            $questionsArray = isset($questionData['questions']) && is_array($questionData['questions'])
                ? $questionData['questions']
                : $questionData;

            foreach ($questionsArray as $question) {
                if (isset($question['question'], $question['answers'])) {
                    // Check if answers array is not empty
                    if (is_array($question['answers']) && !empty($question['answers'])) {
                        $answersOption = array_map(function ($answer) {
                            return [
                                'title' => $answer['title'],
                                'is_correct' => $answer['is_correct']
                            ];
                        }, $question['answers']);

                        $correctAnswer = array_keys(array_filter(array_column($answersOption, 'is_correct')));

                        $data['questions'][] = [
                            'title' => $question['question'],
                            'answers' => $answersOption,
                            'is_correct' => $correctAnswer,
                        ];
                    } else {
                        // For Open Ended questions or questions without answers
                        $data['questions'][] = [
                            'title' => $question['question'],
                            'answers' => [],
                            'is_correct' => [],
                        ];
                        Log::info('Question processed without answers (Open Ended): ' . $question['question']);
                    }
                } else {
                    Log::warning('Invalid question format in AI response: ' . json_encode($question));
                }
            }
        }

        // Always prioritize DB questions over session data for reliability
        if (isset($data['id'])) {
            $dbQuestions = Question::where('quiz_id', $data['id'])->with('answers')->orderBy('id')->get();
            if ($dbQuestions->count() > 0) {
                $data['questions'] = [];
                foreach ($dbQuestions as $question) {
                    $answersOption = $question->answers->map(function ($answer) {
                        return [
                            'title' => $answer->title,
                            'is_correct' => $answer->is_correct,
                        ];
                    })->toArray();
                    $correctAnswer = array_keys(array_filter(array_column($answersOption, 'is_correct')));
                    $data['questions'][] = [
                        'title' => $question->title,
                        'answers' => $answersOption,
                        'is_correct' => $correctAnswer,
                        'question_id' => $question->id,
                    ];
                }
            }
        }
        $this->form->fill($data);
    }

    public function refreshQuestions(): void
    {
        if ($this->record) {
            $this->record->refresh()->load(['questions.answers']);
            // Clear session data to force fresh load from DB
            Session::forget('quizQuestions');
            Session::forget('editedQuizDataForRegeneration');
            
            // Get fresh data from database
            $data = $this->record->attributesToArray();
            $data = $this->mutateFormDataBeforeFill($data);
            
            // Build questions array from DB
            $data['questions'] = [];
            $existingQuestions = \App\Models\Question::where('quiz_id', $this->record->id)
                ->with('answers')
                ->orderBy('id')
                ->get();
                
            foreach ($existingQuestions as $question) {
                $answersOption = $question->answers->map(function ($answer) {
                    return [
                        'title' => $answer->title,
                        'is_correct' => $answer->is_correct,
                    ];
                })->toArray();
                
                $correctAnswer = array_keys(array_filter(array_column($answersOption, 'is_correct')));
                
                $data['questions'][] = [
                    'title' => $question->title,
                    'answers' => $answersOption,
                    'is_correct' => $correctAnswer,
                    'question_id' => $question->id,
                ];
            }
            
            // Force form refresh with new data
            $this->form->fill($data);
            
            // Trigger Livewire re-render
            $this->dispatch('$refresh');
        }
    }

    // Removed background auto-refresh hooks per simplified UX

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('messages.common.back'))
                ->url($this->getResource()::getUrl('index')),
            Action::make('manageTeachers')
                ->label('Manage Teachers')
                ->icon('heroicon-o-user-group')
                ->visible(function(){
                    $sub = getActiveSubscription();
                    return $sub && optional($sub->plan)->multi_teacher;
                })
                ->form([
                    \Filament\Forms\Components\Select::make('teachers')
                        ->label('Co-Teachers')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->options(\App\Models\User::where('id', '!=', auth()->id())->pluck('name','id'))
                        ->default(fn() => $this->record?->teachers()->pluck('users.id')->toArray() ?? [])
                ])
                ->action(function(array $data){
                    if(!$this->record) return;
                    $sub = getActiveSubscription();
                    if(!($sub && optional($sub->plan)->multi_teacher)){
                        Notification::make()->danger()->title('Your plan does not allow multiple teachers.')->send();
                        return;
                    }
                    $teacherIds = $data['teachers'] ?? [];
                    $teacherIds = array_values(array_diff($teacherIds, [auth()->id()]));
                    $this->record->teachers()->sync($teacherIds);
                    Notification::make()->success()->title('Teachers updated.')->send();
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['type'] = getTabType();
        if ($data['type'] == Quiz::TEXT_TYPE) {
            $data['quiz_description'] = $data['quiz_description_text'];
        } elseif ($data['type'] == Quiz::SUBJECT_TYPE) {
            $data['quiz_description'] = $data['quiz_description_sub'];
        } elseif ($data['type'] == Quiz::URL_TYPE) {
            $data['quiz_description'] = $data['quiz_description_url'];
        }
        $questions = array_merge(
            $data['questions'] ?? [],
            $data['custom_questions'] ?? []
        );
        if (!empty($questions)) {

            foreach ($questions as $index => $quizQuestion) {

                // Skip validation for Open Ended questions (type 3) as they don't have predefined answers
                if ($record->quiz_type != Quiz::OPEN_ENDED && (empty($quizQuestion['answers']) || !collect($quizQuestion['answers'])->where('is_correct', true)->count())) {
                    Notification::make()
                        ->danger()
                        ->title('Question #' . ($index + 1) . ' must have at least one correct answer.')
                        ->send();

                    $this->halt();
                }

                if (isset($quizQuestion['question_id'])) {
                    $question = Question::where('quiz_id', $record->id)
                        ->where('id', $quizQuestion['question_id'])
                        ->first();

                    if ($question) {
                        $question->update([
                            'title' => $quizQuestion['title'],
                        ]);
                    } else {
                        $question = Question::create([
                            'quiz_id' => $record->id,
                            'title' => $quizQuestion['title'],
                        ]);
                    }
                } else {
                    $question = Question::create([
                        'quiz_id' => $record->id,
                        'title' => $quizQuestion['title'],
                    ]);
                }
                $updatedQuestionIds[] = $question->id;
                Question::where('quiz_id', $record->id)
                    ->whereNotIn('id', $updatedQuestionIds)
                    ->delete();
                if (!empty($quizQuestion['answers'])) {
                    foreach ($quizQuestion['answers'] as $answer) {
                        $answerRecord = Answer::where('question_id', $question->id)
                            ->where('title', $answer['title'])
                            ->first();

                        if ($answerRecord) {
                            $answerRecord->update([
                                'is_correct' => $answer['is_correct']
                            ]);
                        } else {
                            Answer::create([
                                'question_id' => $question->id,
                                'title' => $answer['title'],
                                'is_correct' => $answer['is_correct']
                            ]);
                        }
                    }
                }
            }
        } else {
            $record->questions()->delete();
        }

        session()->forget('quizQuestions');
        unset($data['questions']);
        unset($data['custom_questions']);
        unset($data['quiz_description_text']);
        unset($data['quiz_description_sub']);
        unset($data['quiz_description_url']);
        unset($data['active_tab']);
        $data['max_questions'] = $record->questions()->count();

        $record->update($data);

        return $record;
    }


    public function getTitle(): string
    {
        return __('messages.quiz.edit_quiz');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.quiz.quiz_updated_success');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            parent::getFormActions()[0],
            Action::make('regenerate')
                ->label(__('messages.common.re_generate'))
                ->color('gray')
                ->action('regenerateQuestions'),

            Action::make('addMoreQuestions')
                ->label('Add More Questions With AI')
                ->color('success')
                ->action(function() { 
                    $this->addMoreQuestions(['count' => 15]); // Default 15 questions
                })
                ->visible(fn() => !Session::has('generating_questions')),

            Action::make('cancel')
                ->label(__('messages.common.cancel'))
                ->color('gray')
                ->url(QuizzesResource::getUrl('index')),

            // Show after questions are added via AI to guide user to edit
            Action::make('edit_questions')
                ->label('Edit Questions')
                ->color('warning')
                ->visible(function(){
                    return Session::has('just_added_questions');
                })
                ->url(fn() => QuizzesResource::getUrl('edit', ['record' => $this->record?->id]))
                ->extraAttributes(['style' => 'margin-left:8px;']),

        ];
    }

    public function addMoreQuestions(array $actionData = []): void
    {
        $additionalQuestions = $actionData['count'] ?? 5;
        
        // Get current quiz data
        $data = $this->record->toArray();
        $description = $this->record->quiz_description;

        // Set description based on the active tab type
        if ($data['type'] == Quiz::TEXT_TYPE) {
            $description = $data['quiz_description_text'] ?? null;
        } elseif ($data['type'] == Quiz::SUBJECT_TYPE) {
            $description = $data['quiz_description_sub'] ?? null;
        } elseif ($data['type'] == Quiz::URL_TYPE && $data['quiz_description_url'] != null) {
            $url = $data['quiz_description_url'];

            $context = stream_context_create([
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
            ]);

            $responseContent = file_get_contents($url, false, $context);
            $readability = new Readability(new Configuration());
            $readability->parse($responseContent);
            $readability->getContent();
            $description = $readability->getExcerpt();
        }

        if (isset($data['quiz_document']) && !empty($data['quiz_document'])) {
            $filePath = $data['quiz_document'];
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($extension === 'pdf') {
                $description = pdfToText($filePath);
                if (empty($description)) {
                    Notification::make()
                        ->warning()
                        ->title('PDF Processing Warning')
                        ->body('PDF text extraction failed. Please try with a different PDF file or use text input instead.')
                        ->persistent()
                        ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                        ->send();
                }
            } elseif ($extension === 'docx') {
                $description = docxToText($filePath);
                if (empty($description)) {
                    Notification::make()
                        ->warning()
                        ->title('DOCX Processing Warning')
                        ->body('DOCX text extraction failed. Please try with a different DOCX file or use text input instead.')
                        ->persistent()
                        ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                        ->send();
                }
            } elseif ($extension === 'pptx') {
                $description = pptxToText($filePath);
                if (empty($description)) {
                    Notification::make()
                        ->warning()
                        ->title('PPTX Processing Warning')
                        ->body('PPTX text extraction failed. Please try with a different PPTX file or use text input instead.')
                        ->persistent()
                        ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                        ->send();
                }
            }
        }

        if (strlen($description) > 10000) {
            $description = substr($description, 0, 10000) . '...';
        }

        // Use requested number of additional questions (respect user input)
        $additionalQuestions = max(1, (int)($actionData['count'] ?? 15));
        $subscription = getActiveSubscription();
        if ($subscription && $subscription->plan) {
            // Safe conversion for max_questions_per_exam
            $maxQuestionsPerExam = 0;
            if (is_numeric($subscription->plan->max_questions_per_exam)) {
                $maxQuestionsPerExam = (int)$subscription->plan->max_questions_per_exam;
            } elseif (is_array($subscription->plan->max_questions_per_exam) && isset($subscription->plan->max_questions_per_exam[0]) && is_numeric($subscription->plan->max_questions_per_exam[0])) {
                $maxQuestionsPerExam = (int)$subscription->plan->max_questions_per_exam[0];
            }
            
            // Check current question count
            $currentQuestionCount = Question::where('quiz_id', $this->record->id)->count();
            
            // Check plan limits and show clear error message
            if ($maxQuestionsPerExam > 0 && ($currentQuestionCount + $additionalQuestions) > $maxQuestionsPerExam) {
                $maxAllowed = $maxQuestionsPerExam - $currentQuestionCount;
                if ($maxAllowed <= 0) {
                    Notification::make()
                        ->danger()
                        ->icon('heroicon-o-exclamation-triangle')
                        ->title('Question Limit Reached')
                        ->body('You have reached the maximum number of questions allowed for your plan.')
                        ->persistent()
                        ->actions([
                            NotificationAction::make('close')
                                ->label('Close')
                                ->button()
                                ->color('gray')
                                ->close(),
                        ])
                        ->send();
                    return;
                } else {
                    Notification::make()
                        ->danger()
                        ->icon('heroicon-o-exclamation-triangle')
                        ->title('Question Limit Exceeded')
                        ->body("You requested {$additionalQuestions} questions, but your plan allows only {$maxAllowed} more questions. Current: {$currentQuestionCount}, Plan limit: {$maxQuestionsPerExam}")
                        ->persistent()
                        ->actions([
                            NotificationAction::make('close')
                                ->label('Close')
                                ->button()
                                ->color('gray')
                                ->close(),
                        ])
                        ->send();
                    return;
                }
            }
        }

        // Use a distinct variable for metadata to avoid collisions with $quizData JSON later
        $quizMeta = [
            'Difficulty' => Quiz::DIFF_LEVEL[$data['diff_level']],
            'question_type' => Quiz::QUIZ_TYPE[$data['quiz_type']],
            'language' => getAllLanguages()[$data['language']] ?? 'English'
        ];

        $prompt = <<<PROMPT

        You are an expert in crafting engaging quizzes. Generate exactly {$additionalQuestions} additional questions according to the specified question type.

        STRICT OUTPUT REQUIREMENTS:
        - Output MUST be a JSON array with LENGTH exactly {$additionalQuestions}. Do not exceed or go under.
        - Do NOT include any surrounding prose, markdown, headings, or keys other than the array itself.
        - If you produce more than {$additionalQuestions} internally, RETURN ONLY the first {$additionalQuestions} items.

        **Quiz Details:**

        - **Title**: {$data['title']}
        - **Description**: {$description}
        - **Number of Additional Questions**: {$additionalQuestions}
        - **Difficulty**: {$quizMeta['Difficulty']}
        - **Question Type**: {$quizMeta['question_type']}
        - **Language**: {$quizMeta['language']}

        **CRITICAL LANGUAGE REQUIREMENT:**
        - You MUST write ALL questions and answers EXCLUSIVELY in {$quizMeta['language']} language.
        - Do NOT use English or any other language.
        - If the language is "Hindi", write everything in Hindi (Devanagari script).
        - If the language is "Arabic", write everything in Arabic script.
        - If the language is "Spanish", write everything in Spanish.
        - This is MANDATORY - every single word must be in the specified language.

        **CRITICAL ANSWER REQUIREMENT:**
        - You MUST provide answers for ALL questions except Open Ended questions.
        - For Single Choice, Multiple Choice, and True/False questions, you MUST include the answers array with proper options.
        - Do NOT create questions without answers unless they are specifically Open Ended questions.
        - Each answer must have a "title" field and an "is_correct" field (true/false).

        **Instructions:**

        1. **Language Requirement**: Write all quiz questions and answers in {$quizMeta['language']}.
        2. **Number of Questions**: Create exactly {$additionalQuestions} additional questions. Return an array of length {$additionalQuestions} only.
        3. **Difficulty Level**: Ensure each question adheres to the specified difficulty level: {$quizMeta['Difficulty']}.
        4. **Description Alignment**: Ensure that each question is relevant to and reflects key aspects of the provided description.
        5. **Question Type**: Follow the format specified below based on the question type:

        **Question Formats:**

        - **Multiple Choice**:
            - Structure your JSON with four answer options. Mark exactly two options as `is_correct: true`. Use the following format:

            [
                {
                    "question": "Your question text here",
                    "answers": [
                        {
                            "title": "Answer Option 1",
                            "is_correct": false
                        },
                        {
                            "title": "Answer Option 2",
                            "is_correct": true
                        },
                        {
                            "title": "Answer Option 3",
                            "is_correct": false
                        },
                        {
                            "title": "Answer Option 4",
                            "is_correct": true
                        }
                    ],
                    "correct_answer_key": ["Answer Option 2", "Answer Option 4"]
                }
            ]

        - **Single Choice**:
            - Use the following format with exactly four options. Mark one option as `is_correct: true` and the other three as `is_correct: false`:

            [
                {
                    "question": "Your question text here",
                    "answers": [
                        {
                            "title": "Answer Option 1",
                            "is_correct": false
                        },
                        {
                            "title": "Answer Option 2",
                            "is_correct": true
                        },
                        {
                            "title": "Answer Option 3",
                            "is_correct": false
                        },
                        {
                            "title": "Answer Option 4",
                            "is_correct": false
                        }
                    ],
                    "correct_answer_key": "Answer Option 2"
                }
            ]

        - **True/False**:
            - Use the following format with exactly two options (True and False). Mark one option as `is_correct: true`:

            [
                {
                    "question": "Your question text here",
                    "answers": [
                        {
                            "title": "True",
                            "is_correct": true
                        },
                        {
                            "title": "False",
                            "is_correct": false
                        }
                    ],
                    "correct_answer_key": "True"
                }
            ]

        - **Open Ended**:
            - Use the following format with no predefined answers. The user will provide their own answer:

            [
                {
                    "question": "Your question text here",
                    "answers": [],
                    "correct_answer_key": "User will provide their own answer"
                }
            ]

        **Guidelines:**
        - You must generate exactly **{$additionalQuestions}** additional questions.
        - For Multiple Choice questions, ensure that there are exactly four answer options, with two options marked as `is_correct: true`.
        - For Single Choice questions, ensure that there are exactly four answer options, with one option marked as `is_correct: true`.
        - For True/False questions, ensure that there are exactly two answer options (True and False), with one option marked as `is_correct: true`.
        - For Open Ended questions, provide no answer options (empty answers array).
        - The correct_answer_key should match the correct answer's title value(s) for Multiple Choice, Single Choice, and True/False questions.
        - Ensure that each question is diverse and well-crafted, covering various relevant concepts.
        - **LANGUAGE COMPLIANCE**: Every single word in questions and answers MUST be in {$quizMeta['language']}. No exceptions.

        Your responses should be formatted impeccably in JSON, capturing the essence of the provided quiz details.

        PROMPT;

        $aiType = getSetting()->ai_type;

        if ($aiType == Quiz::GEMINI_AI) {
            $geminiApiKey = getSetting()->gemini_api_key;
            $model = getSetting()->gemini_ai_model;

            if (! $geminiApiKey) {
                Notification::make()
                    ->danger()
                    ->title(__('messages.quiz.set_openai_key_at_env'))
                    ->send();
                return;
            }

            // Set session flag to show generating state
            Session::put('generating_questions', true);
            Session::put('generating_count', $additionalQuestions);
            
            // Force page refresh to show progress card
            $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record->id]));

            $geminiResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$geminiApiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);

            if ($geminiResponse->failed()) {
                Notification::make()
                    ->danger()
                    ->title($geminiResponse->json()['error']['message'])
                    ->send();
                return;
            }

            $rawText = $geminiResponse->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
            $quizText = preg_replace('/^```(?:json)?|```$/im', '', $rawText);
        }
        if ($aiType == Quiz::OPEN_AI) {
            $key = getSetting()->open_api_key;
            $openAiKey = (! empty($key)) ? $key : config('services.open_ai.open_api_key');
            $model = getSetting()->open_ai_model;

            if (! $openAiKey) {
                Notification::make()
                    ->danger()
                    ->title(__('messages.quiz.set_openai_key_at_env'))
                    ->send();
                return;
            }

            // Set session flag to show generating state
            Session::put('generating_questions', true);
            Session::put('generating_count', $additionalQuestions);
            
            // Force page refresh to show progress card
            $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record->id]));

            // Dispatch background job instead of doing long-running HTTP work in request
            GenerateAdditionalQuestions::dispatch(
                $this->record->id,
                $additionalQuestions,
                (string)$description,
                [
                    'title' => $data['title'],
                    'difficulty' => $quizMeta['Difficulty'],
                    'question_type' => $quizMeta['question_type'],
                    'language' => $quizMeta['language'],
                    'ai_type' => Quiz::OPEN_AI,
                    'open_ai_key' => $openAiKey,
                    'open_ai_model' => $model,
                ]
            );
            // Start live progress polling on client via temporary state field
            Session::put('gen_progress_key', "quiz:".$this->record->id.":gen_progress");
            return;
        }

        Log::info("Additional questions AI response received: " . ($quizText ? 'yes' : 'no'));

        if ($quizText) {
            $quizData = trim($quizText);
            Log::info("Raw additional questions AI response: " . substr($quizData, 0, 500));
            
            if (stripos($quizData, '```json') === 0) {
                $quizData = preg_replace('/^```json\s*|\s*```$/', '', $quizData);
                $quizData = trim($quizData);
            }
            $quizQuestions = json_decode($quizData, true);
            
            Log::info("Parsed additional questions count: " . (is_array($quizQuestions) ? count($quizQuestions) : 'not array'));
            Log::info("JSON decode error: " . json_last_error_msg());

                if (is_array($quizQuestions)) {
                $addedCount = 0;
                foreach ($quizQuestions as $index => $question) {
                    if ($addedCount >= $additionalQuestions) { break; }
                    Log::info("Processing additional question " . (intval($index) + 1) . ": " . json_encode($question));
                    
                    // Check if this is a nested array of questions
                    if (is_array($question) && isset($question[0]) && is_array($question[0]) && isset($question[0]['question'])) {
                        Log::info("Found nested questions array, processing " . count($question) . " questions");
                        foreach ($question as $nestedIndex => $nestedQuestion) {
                            if ($addedCount >= $additionalQuestions) { break; }
                            if (isset($nestedQuestion['question'], $nestedQuestion['answers'])) {
                                $questionModel = Question::create([
                                    'quiz_id' => $this->record->id,
                                    'title' => $nestedQuestion['question'],
                                ]);

                                // Check if answers array is not empty
                                if (is_array($nestedQuestion['answers']) && !empty($nestedQuestion['answers'])) {
                                    foreach ($nestedQuestion['answers'] as $answer) {
                                        $isCorrect = false;
                                        $correctKey = $nestedQuestion['correct_answer_key'] ?? '';

                                        if (is_array($correctKey)) {
                                            $isCorrect = in_array($answer['title'], $correctKey);
                                        } else {
                                            $isCorrect = $answer['title'] === $correctKey;
                                        }

                                        Answer::create([
                                            'question_id' => $questionModel->id,
                                            'title' => $answer['title'],
                                            'is_correct' => $isCorrect,
                                        ]);
                                    }
                                    Log::info("Additional nested question created successfully with " . count($nestedQuestion['answers']) . " answers");
                                } else {
                                    // For Open Ended questions or questions without answers
                                    Log::info('Additional nested question created without answers (Open Ended): ' . $nestedQuestion['question']);
                                }
                                $addedCount++;
                            } else {
                                Log::warning('Invalid nested question format in AI response: ' . json_encode($nestedQuestion));
                            }
                        }
                    } elseif (isset($question['question'], $question['answers'])) {
                        if ($addedCount >= $additionalQuestions) { break; }
                        $questionModel = Question::create([
                            'quiz_id' => $this->record->id,
                            'title' => $question['question'],
                        ]);

                        // Check if answers array is not empty
                        if (is_array($question['answers']) && !empty($question['answers'])) {
                            foreach ($question['answers'] as $answer) {
                                $isCorrect = false;
                                $correctKey = $question['correct_answer_key'] ?? '';

                                if (is_array($correctKey)) {
                                    $isCorrect = in_array($answer['title'], $correctKey);
                                } else {
                                    $isCorrect = $answer['title'] === $correctKey;
                                }

                                Answer::create([
                                    'question_id' => $questionModel->id,
                                    'title' => $answer['title'],
                                    'is_correct' => $isCorrect,
                                ]);
                            }
                            Log::info("Additional question created successfully with " . count($question['answers']) . " answers");
                        } else {
                            // For Open Ended questions or questions without answers
                            Log::info('Additional question created without answers (Open Ended): ' . $question['question']);
                        }
                        $addedCount++;
                    } else {
                        Log::warning('Invalid question format in AI response: ' . json_encode($question));
                        
                        // Check if this is a string (like "class 8 hindi exam") and skip it
                        if (is_string($question)) {
                            Log::info("Skipping string element: " . $question);
                            continue;
                        }
                        
                        // Only log keys if it's an array
                        if (is_array($question)) {
                            Log::warning('Question keys: ' . implode(', ', array_keys($question)));
                        }
                    }
                }
                // If model returned fewer than requested, try once more for the remaining
                if ($addedCount < $additionalQuestions && $aiType == Quiz::OPEN_AI) {
                    $remaining = $additionalQuestions - $addedCount;
                    Log::info("Attempting second request for remaining questions: {$remaining}");
                    $prompt2 = <<<PROMPT

                    You are an expert in crafting engaging quizzes. Generate exactly {$remaining} additional questions according to the specified question type.

                    STRICT OUTPUT REQUIREMENTS:
                    - Output MUST be a JSON array with LENGTH exactly {$remaining}. Do not exceed or go under.
                    - Do NOT include any surrounding prose, markdown, headings, or keys other than the array itself.
                    - If you produce more than {$remaining} internally, RETURN ONLY the first {$remaining} items.

                    **Quiz Details:**

                    - **Title**: {$data['title']}
                    - **Description**: {$description}
                    - **Number of Additional Questions**: {$remaining}
                    - **Difficulty**: {$quizMeta['Difficulty']}
                    - **Question Type**: {$quizMeta['question_type']}
                    - **Language**: {$quizMeta['language']}

                    [Return ONLY the JSON array as described.]

                    PROMPT;

                    try {
                        $resp2 = Http::withToken($openAiKey)
                            ->withHeaders(['Content-Type' => 'application/json'])
                            ->connectTimeout(20)
                            ->timeout(180)
                            ->retry(3, 2000)
                            ->post('https://api.openai.com/v1/chat/completions', [
                                'model' => $model,
                                'temperature' => 0.7,
                                'max_tokens' => 12000,
                                'messages' => [['role' => 'user', 'content' => $prompt2]],
                            ]);

                        if ($resp2->successful()) {
                            $txt2 = $resp2['choices'][0]['message']['content'] ?? '';
                            $txt2 = trim(preg_replace('/^```json\s*|\s*```$/', '', $txt2));
                            $arr2 = json_decode($txt2, true);
                            if (is_array($arr2)) {
                                foreach ($arr2 as $q) {
                                    if ($addedCount >= $additionalQuestions) { break; }
                                    if (isset($q['question'], $q['answers'])) {
                                        $questionModel = Question::create([
                                            'quiz_id' => $this->record->id,
                                            'title' => $q['question'],
                                        ]);
                                        if (is_array($q['answers']) && !empty($q['answers'])) {
                                            $correctKey = $q['correct_answer_key'] ?? '';
                                            foreach ($q['answers'] as $ans) {
                                                $isCorrect = is_array($correctKey) ? in_array($ans['title'], $correctKey) : ($ans['title'] === $correctKey);
                                                Answer::create([
                                                    'question_id' => $questionModel->id,
                                                    'title' => $ans['title'],
                                                    'is_correct' => $isCorrect,
                                                ]);
                                            }
                                        }
                                        $addedCount++;
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Second request for remaining questions failed: ' . $e->getMessage());
                    }
                }
                
                // Clear session flags
                Session::forget(['generating_questions', 'generating_count']);
                
                if ($addedCount > 0) {
                    // Hard refresh to ensure Livewire state fully reloads with DB
                    $this->record->refresh();
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record->id]));
                } else {
                    Notification::make()
                        ->warning()
                        ->title('No Questions Added')
                        ->body('No additional questions were generated. Please try again.')
                        ->persistent()
                        ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                        ->send();
                }
            } else {
                Log::error('AI response is not a valid array: ' . $quizData);
                Log::error('JSON decode error: ' . json_last_error_msg());
                // Clear session flags on error
                Session::forget(['generating_questions', 'generating_count']);
                Notification::make()
                    ->danger()
                    ->title('Error Adding Questions')
                    ->body('Failed to generate additional questions. Please try again.')
                    ->persistent()
                    ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                    ->send();
            }
        } else {
            Log::error('No AI response received for additional questions - quizText is empty or null');
            // Clear session flags on error
            Session::forget(['generating_questions', 'generating_count']);
            Notification::make()
                ->danger()
                ->title('Error Adding Questions')
                ->body('Failed to generate additional questions. Please try again.')
                ->persistent()
                ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                ->send();
        }
    }

    public function regenerateQuestions(): void
    {
        $currentFormState = $this->form->getState();
        $currentFormState['type'] = getTabType();
        if ($currentFormState['type'] == Quiz::TEXT_TYPE) {
            $currentFormState['quiz_description'] = $currentFormState['quiz_description_text'];
        } elseif ($currentFormState['type'] == Quiz::SUBJECT_TYPE) {
            $currentFormState['quiz_description'] = $currentFormState['quiz_description_sub'];
        } elseif ($currentFormState['type'] == Quiz::URL_TYPE) {
            $currentFormState['quiz_description'] = $currentFormState['quiz_description_url'];
        }
        Session::put('editedQuizDataForRegeneration', $currentFormState);

        $data = $this->data;
        $description = null;

        // Set description based on the active tab type
        if ($data['type'] == Quiz::TEXT_TYPE) {
            $description = $data['quiz_description_text'] ?? null;
        } elseif ($data['type'] == Quiz::SUBJECT_TYPE) {
            $description = $data['quiz_description_sub'] ?? null;
        } elseif ($data['type'] == Quiz::URL_TYPE && $data['quiz_description_url'] != null) {
            $url = $data['quiz_description_url'];

            $context = stream_context_create([
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
            ]);

            $responseContent = file_get_contents($url, false, $context);
            $readability = new Readability(new Configuration());
            $readability->parse($responseContent);
            $readability->getContent();
            $description = $readability->getExcerpt();
        }

        if (isset($data['quiz_document']) && !empty($data['quiz_document'])) {
            $filePath = $data['quiz_document'];
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($extension === 'pdf') {
                // Check PDF page count limit
                $pageCount = getPdfPageCount($filePath);
                if (!is_null($subscription->plan->max_pdf_pages) && (int)$subscription->plan->max_pdf_pages > 0) {
                    if ($pageCount > $subscription->plan->max_pdf_pages) {
                        Notification::make()
                            ->danger()
                            ->title('PDF Page Limit Exceeded')
                            ->body("PDF has {$pageCount} pages, but your plan allows maximum {$subscription->plan->max_pdf_pages} pages. Please upgrade your plan or use a smaller PDF.")
                            ->send();
                        return;
                    }
                }

                $description = pdfToText($filePath);
                if (empty($description)) {
                    Notification::make()
                        ->warning()
                        ->title('PDF Processing Warning')
                        ->body('PDF text extraction failed. Please try with a different PDF file or use text input instead.')
                        ->send();
                }
            } elseif ($extension === 'docx') {
                $description = docxToText($filePath);
                if (empty($description)) {
                    Notification::make()
                        ->warning()
                        ->title('DOCX Processing Warning')
                        ->body('DOCX text extraction failed. Please try with a different DOCX file or use text input instead.')
                        ->send();
                }
            }
        }

        if (strlen($description) > 10000) {
            $description = substr($description, 0, 10000) . '...';
        }

        // Enforce per-exam limit in edit regenerate flow as well
        $subscription = getActiveSubscription();
        if ($subscription && $subscription->plan && !is_null($data['max_questions'])) {
            $limit = $subscription->plan->max_questions_per_exam;
            if (!is_null($limit) && (int)$limit >= 0 && $data['max_questions'] > $limit) {
                $data['max_questions'] = $limit;
            }
        }

        $quizMeta = [
            'Difficulty' => Quiz::DIFF_LEVEL[$data['diff_level']],
            'question_type' => Quiz::QUIZ_TYPE[$data['quiz_type']],
            'language' => getAllLanguages()[$data['language']] ?? 'English'
        ];

        $prompt = <<<PROMPT

        You are an expert in crafting engaging quizzes. Based on the quiz details provided, your task is to meticulously generate questions according to the specified question type. Your output should be exclusively in properly formatted JSON.

        **Quiz Details:**

        - **Title**: {$data['title']}
        - **Description**: {$description}
        - **Number of Questions**: {$data['max_questions']}
        - **Difficulty**: {$quizMeta['Difficulty']}
        - **Question Type**: {$quizMeta['question_type']}
        - **Language**: {$quizMeta['language']}

        **CRITICAL LANGUAGE REQUIREMENT:**
        - You MUST write ALL questions and answers EXCLUSIVELY in {$quizMeta['language']} language.
        - Do NOT use English or any other language.
        - If the language is "Hindi", write everything in Hindi (Devanagari script).
        - If the language is "Arabic", write everything in Arabic script.
        - If the language is "Spanish", write everything in Spanish.
        - This is MANDATORY - every single word must be in the specified language.

        **CRITICAL ANSWER REQUIREMENT:**
        - You MUST provide answers for ALL questions except Open Ended questions.
        - For Single Choice, Multiple Choice, and True/False questions, you MUST include the answers array with proper options.
        - Do NOT create questions without answers unless they are specifically Open Ended questions.
        - Each answer must have a "title" field and an "is_correct" field (true/false).

        **Instructions:**

        1. **Language Requirement**: Write all quiz questions and answers in {$quizMeta['language']}.
        2. **Number of Questions**: Create exactly {$data['max_questions']} questions.
        3. **Difficulty Level**: Ensure each question adheres to the specified difficulty level: {$quizMeta['Difficulty']}.
        4. **Description Alignment**: Ensure that each question is relevant to and reflects key aspects of the provided description.
        5. **Question Type**: Follow the format specified below based on the question type:

        **Question Formats:**

        - **Multiple Choice**:
            - Structure your JSON with four answer options. Mark exactly two options as `is_correct: true`. Use the following format:

            [
                {
                    "question": "Your question text here",
                    "answers": [
                        {
                            "title": "Answer Option 1",
                            "is_correct": false
                        },
                        {
                            "title": "Answer Option 2",
                            "is_correct": true
                        },
                        {
                            "title": "Answer Option 3",
                            "is_correct": false
                        },
                        {
                            "title": "Answer Option 4",
                            "is_correct": true
                        }
                    ],
                    "correct_answer_key": ["Answer Option 2", "Answer Option 4"]
                }
            ]

        - **Single Choice**:
            - Use the following format with exactly four options. Mark one option as `is_correct: true` and the other three as `is_correct: false`:

            [
                {
                    "question": "Your question text here",
                    "answers": [
                        {
                            "title": "Answer Option 1",
                            "is_correct": false
                        },
                        {
                            "title": "Answer Option 2",
                            "is_correct": true
                        },
                        {
                            "title": "Answer Option 3",
                            "is_correct": false
                        },
                        {
                            "title": "Answer Option 4",
                            "is_correct": false
                        }
                    ],
                    "correct_answer_key": "Answer Option 2"
                }
            ]

        - **True/False**:
            - Use the following format with exactly two options (True and False). Mark one option as `is_correct: true`:

            [
                {
                    "question": "Your question text here",
                    "answers": [
                        {
                            "title": "True",
                            "is_correct": true
                        },
                        {
                            "title": "False",
                            "is_correct": false
                        }
                    ],
                    "correct_answer_key": "True"
                }
            ]

        - **Open Ended**:
            - Use the following format with no predefined answers. The user will provide their own answer:

            [
                {
                    "question": "Your question text here",
                    "answers": [],
                    "correct_answer_key": "User will provide their own answer"
                }
            ]

        **Guidelines:**
        - You must generate exactly **{$data['max_questions']}** questions.
        - For Multiple Choice questions, ensure that there are exactly four answer options, with two options marked as `is_correct: true`.
        - For Single Choice questions, ensure that there are exactly four answer options, with one option marked as `is_correct: true`.
        - For True/False questions, ensure that there are exactly two answer options (True and False), with one option marked as `is_correct: true`.
        - For Open Ended questions, provide no answer options (empty answers array).
        - The correct_answer_key should match the correct answer's title value(s) for Multiple Choice, Single Choice, and True/False questions.
        - Ensure that each question is diverse and well-crafted, covering various relevant concepts.
        - **LANGUAGE COMPLIANCE**: Every single word in questions and answers MUST be in {$quizMeta['language']}. No exceptions.

        Your responses should be formatted impeccably in JSON, capturing the essence of the provided quiz details.

        PROMPT;

        $aiType = getSetting()->ai_type;
        $quizText = null;

        if ($aiType === Quiz::GEMINI_AI) {
            $geminiApiKey = getSetting()->gemini_api_key;
            $model = getSetting()->gemini_ai_model;

            if (!$geminiApiKey) {
                Notification::make()->danger()->title(__('messages.quiz.set_openai_key_at_env'))->send();
                return;
            }

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$geminiApiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

                if ($response->failed()) {
                    Notification::make()->danger()->title($response->json()['error']['message'])->send();
                    return;
                }

                $rawText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                $quizText = preg_replace('/^```(?:json)?|```$/im', '', $rawText);
            } catch (\Exception $exception) {
                Notification::make()->danger()->title($exception->getMessage())->send();
                return;
            }
        }

        if ($aiType === Quiz::OPEN_AI) {
            $key = getSetting()->open_api_key ?? null;
            $openAiKey = ! empty($key) ? $key : config('services.open_ai.open_api_key');
            $model = getSetting()->open_ai_model;

            if (!$openAiKey) {
                Notification::make()->danger()->title(__('messages.quiz.set_openai_key_at_env'))->send();
                return;
            }

            try {
                $quizResponse = Http::withToken($openAiKey)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->connectTimeout(20)
                    ->timeout(180)
                    ->retry(3, 2000)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'messages' => [['role' => 'user', 'content' => $prompt]]
                    ]);

                if ($quizResponse->failed()) {
                    $error = $quizResponse->json()['error']['message'] ?? 'Unknown error occurred';
                    Notification::make()->danger()->title(__('OpenAI Error'))->body($error)->send();
                    return;
                }

                $quizText = $quizResponse['choices'][0]['message']['content'] ?? null;
            } catch (\Exception $e) {
                Notification::make()->danger()->title(__('API Request Failed'))->body($e->getMessage())->send();
                Log::error('OpenAI API error: ' . $e->getMessage());
                return;
            }
        }

        if ($quizText) {
            Session::put('quizQuestions', $quizText);
            $this->fillForm();
        } else {
            Notification::make()
                ->danger()
                ->title('Quiz generation failed.')
                ->send();
        }
    }
}
