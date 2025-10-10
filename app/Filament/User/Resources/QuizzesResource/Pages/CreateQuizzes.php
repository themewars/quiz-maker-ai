<?php

namespace App\Filament\User\Resources\QuizzesResource\Pages;

use App\Filament\User\Resources\QuizzesResource;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use fivefilters\Readability\Configuration;
use fivefilters\Readability\Readability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Filament\Notifications\Actions\Action as NotificationAction;

class CreateQuizzes extends CreateRecord
{
    protected static string $resource = QuizzesResource::class;

    protected static bool $canCreateAnother = false;

    public static $tab = Quiz::TEXT_TYPE;

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

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->extraAttributes([
                'x-data' => '{}',
                'x-on:click' => '$nextTick(()=>{ const s = $el.querySelector(".fi-btn-label"); if (s) s.textContent = "Please Wait"; })',
                'wire:loading.attr' => 'disabled',
                'wire:target' => 'create',
                'wire:loading.class' => 'opacity-70 cursor-not-allowed',
            ]);
    }

    // Duplicate removed; using getCreateFormAction() customization instead

    protected function handleRecordCreation(array $data): Model
    {
        $userId = Auth::id();
        $subscription = getActiveSubscription();
        if (!$subscription || !$subscription->plan) {
            Notification::make()->danger()->title(__('messages.plan.your_plan_expired_and_choose_plan'))
                ->persistent()
                ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                ->send();
            $this->halt();
        }

        // Enforce question type allowance
        $allowedTypes = (array) ($subscription->plan->allowed_question_types ?? []);
        $incomingType = $data['quiz_type'] ?? Quiz::MULTIPLE_CHOICE;
        $map = [
            Quiz::MULTIPLE_CHOICE => 'multiple_choice',
            Quiz::SINGLE_CHOICE => 'single_choice',
        ];
        if (!empty($allowedTypes) && isset($map[$incomingType]) && !in_array($map[$incomingType], $allowedTypes)) {
            Notification::make()->danger()->title(__('This question type is not allowed in your plan.'))
                ->persistent()
                ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                ->send();
            $this->halt();
        }

        // Apply default questions: respect user input first, then use defaults
        $userDefaultCount = (int) (getUserSettings('default_questions_count') ?? 10);
        $userInputQuestions = (int) ($data['max_questions'] ?? 0);
        
        // Use user input if provided, otherwise use defaults
        $maxQuestions = $userInputQuestions > 0 
            ? $userInputQuestions 
            : (($subscription && $subscription->plan) ? 25 : max(1, $userDefaultCount));
        
        // Safe conversion for max_questions_per_exam
        $maxQuestionsPerExam = 0;
        if (is_numeric($subscription->plan->max_questions_per_exam)) {
            $maxQuestionsPerExam = (int)$subscription->plan->max_questions_per_exam;
        } elseif (is_array($subscription->plan->max_questions_per_exam) && isset($subscription->plan->max_questions_per_exam[0]) && is_numeric($subscription->plan->max_questions_per_exam[0])) {
            $maxQuestionsPerExam = (int)$subscription->plan->max_questions_per_exam[0];
        }
        
        // Ensure we don't exceed plan limits
        if ($maxQuestionsPerExam > 0 && $maxQuestions > $maxQuestionsPerExam) {
            $maxQuestions = $maxQuestionsPerExam;
        }

        // Initial generation cap for paid plans: maximum 25 on create
        if ($subscription && $subscription->plan && $maxQuestions > 25) {
            $maxQuestions = 25;
        }
        
        // Update data for AI prompt
        $data['max_questions'] = $maxQuestions;
        
        // Safe conversion for max_questions_per_month
        $maxQuestionsPerMonth = 0;
        if (is_numeric($subscription->plan->max_questions_per_month)) {
            $maxQuestionsPerMonth = (int)$subscription->plan->max_questions_per_month;
        } elseif (is_array($subscription->plan->max_questions_per_month) && isset($subscription->plan->max_questions_per_month[0]) && is_numeric($subscription->plan->max_questions_per_month[0])) {
            $maxQuestionsPerMonth = (int)$subscription->plan->max_questions_per_month[0];
        }
        
        if ($maxQuestionsPerMonth >= 0) {
            $periodStart = now()->startOfMonth();
            $periodEnd = now()->endOfMonth();
            // questions table doesn't have user_id; filter via related quiz
            $monthQuestions = Question::whereBetween('created_at', [$periodStart, $periodEnd])
                ->whereHas('quiz', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->count();
            if ($monthQuestions >= $maxQuestionsPerMonth) {
                Notification::make()->danger()->title(__('You have reached your monthly question limit.'))
                    ->persistent()
                    ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                    ->send();
                $this->halt();
            }
        }
        $activeTab = getTabType();

        $descriptionFields = [
            Quiz::TEXT_TYPE => $data['quiz_description_text'] ?? null,
            Quiz::SUBJECT_TYPE => $data['quiz_description_sub'] ?? null,
            Quiz::URL_TYPE => $data['quiz_description_url'] ?? null,
        ];

        $description = $descriptionFields[$activeTab] ?? null;
        
        // Debug logging
        Log::info("=== EXAM CREATION DEBUG START ===");
        Log::info("Active tab: " . $activeTab . ", Description: " . ($description ?? 'null'));
        Log::info("Form data keys: " . implode(', ', array_keys($data)));
        Log::info("quiz_description_sub: " . ($data['quiz_description_sub'] ?? 'not set'));
        Log::info("quiz_description_text: " . ($data['quiz_description_text'] ?? 'not set'));
        Log::info("AI Type: " . getSetting()->ai_type);
        Log::info("AI API Key exists: " . (getSetting()->gemini_api_key ? 'yes' : 'no'));
        Log::info("Max questions for AI: " . $maxQuestions);
        Log::info("=== EXAM CREATION DEBUG END ===");

        $input = [
            'user_id' => $userId,
            'title' => $data['title'],
            'category_id' => $data['category_id'],
            'quiz_description' => $description,
            'type' => $activeTab,
            'status' => 1,
            'quiz_type' => $data['quiz_type'] ?? (int)(getUserSettings('default_question_type') ?? 0),
            'max_questions' => $maxQuestions,
            'diff_level' => $data['diff_level'] ?? (int)(getUserSettings('default_difficulty') ?? 0),
            'unique_code' => generateUniqueCode(),
            'language' => $data['language'] ?? (getUserSettings('default_language') ?? 'en'),
            'time_configuration' => $data['time_configuration'] ?? 0,
            'time' => $data['time'] ?? 0,
            'time_type' => $data['time_type'] ?? null,
            'quiz_expiry_date' => $data['quiz_expiry_date'] ?? null,
            'is_public' => $data['is_public'] ?? ((int)(getUserSettings('public_default') ?? 0) === 1),
            // If public, auto show on home
            'is_show_home' => ($data['is_public'] ?? ((int)(getUserSettings('public_default') ?? 0) === 1)) ? true : ((int)(getUserSettings('show_on_home_default') ?? 0) === 1),
        ];

        if ($activeTab == Quiz::URL_TYPE && $data['quiz_description_url'] != null) {
            $url = $data['quiz_description_url'];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode != 200) {
                throw new \Exception('Failed to fetch the URL content. HTTP Code: '.$httpCode);
            }

            $readability = new Readability(new Configuration);
            $readability->parse($response);
            $readability->getContent();
            $description = $readability->getExcerpt();
        }

        if (isset($this->data['file_upload']) && is_array($this->data['file_upload'])) {
            foreach ($this->data['file_upload'] as $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    // Check file size limit based on active subscription plan (MB -> bytes)
                    $planMb = 10; // default fallback
                    if ($subscription && $subscription->plan && is_numeric($subscription->plan->max_pdf_upload_mb)) {
                        $planMb = max(1, (int) $subscription->plan->max_pdf_upload_mb);
                    }
                    $maxFileSize = $planMb * 1024 * 1024;
                    if ($file->getSize() > $maxFileSize) {
                        Notification::make()
                            ->danger()
                            ->title('File Size Exceeded')
                            ->body('File size exceeds the maximum allowed limit of ' . $planMb . 'MB. Please upload a smaller file.')
                            ->persistent()
                            ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                            ->send();
                        $this->halt();
                    }

                    // Check PDF page count limit before storing
                    $extension = strtolower($file->getClientOriginalExtension());
                    if ($extension === 'pdf') {
                        $tempPath = $file->getRealPath();
                        $pageCount = getPdfPageCount($tempPath);
                        $planLimit = $subscription->plan->max_pdf_pages;
                        
                        // Convert plan limit to integer safely
                        $planLimitInt = 0;
                        if (is_numeric($planLimit)) {
                            $planLimitInt = (int)$planLimit;
                        } elseif (is_array($planLimit) && isset($planLimit[0]) && is_numeric($planLimit[0])) {
                            $planLimitInt = (int)$planLimit[0];
                        }
                        
                        Log::info("PDF page count: " . $pageCount . ", Plan limit: " . $planLimitInt);
                        
                        if ($planLimitInt > 0 && $pageCount > $planLimitInt) {
                            Log::warning("PDF page limit exceeded: " . $pageCount . " > " . $planLimitInt);
                            Notification::make()
                                ->danger()
                                ->title('PDF Page Limit Exceeded')
                                ->body("PDF has " . $pageCount . " pages, but your plan allows maximum " . $planLimitInt . " pages. Please upgrade your plan or use a smaller PDF.")
                                ->persistent()
                                ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                                ->send();
                            $this->halt();
                        } else {
                            Log::info("PDF page count validation passed: " . $pageCount . " <= " . $planLimitInt);
                        }
                    }

                    $filePath = $file->store('temp-file', 'public');
                    $fileUrl = Storage::disk('public')->url($filePath);

                    if ($extension === 'pdf') {
                        $description = pdfToText($fileUrl);
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
                        $description = docxToText($fileUrl);
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
                        $description = pptxToText($fileUrl);
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
            }
        }

        if (strlen($description) > 10000) {
            $description = substr($description, 0, 10000).'...';
        }

        $quizData = [
            'Title' => $data['title'],
            'Description' => $description,
            'No of Questions' => $data['max_questions'],
            'Difficulty' => Quiz::DIFF_LEVEL[$data['diff_level']],
            'question_type' => Quiz::QUIZ_TYPE[$data['quiz_type']],
            'language' => getAllLanguages()[$data['language']] ?? 'English',
        ];

        $prompt = <<<PROMPT

    You are an expert in crafting engaging quizzes. Based on the quiz details provided, your task is to meticulously generate questions according to the specified question type. Your output should be exclusively in properly formatted JSON.

    **Quiz Details:**

    - **Title**: {$data['title']}
    - **Description**: {$description}
    - **Number of Questions**: {$data['max_questions']}
    - **Difficulty**: {$quizData['Difficulty']}
    - **Question Type**: {$quizData['question_type']}
    - **Language**: {$quizData['language']}

    **CRITICAL LANGUAGE REQUIREMENT:**
    - You MUST write ALL questions and answers EXCLUSIVELY in {$quizData['language']} language.
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

    1. **Language Requirement**: Write all quiz questions and answers in {$quizData['language']}.
    2. **Number of Questions**: Create exactly {$data['max_questions']} questions.
    3. **Difficulty Level**: Ensure each question adheres to the specified difficulty level: {$quizData['Difficulty']}.
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

        **CRITICAL QUESTION TYPE ENFORCEMENT:**
        - You MUST generate ALL questions as **{$quizData['question_type']}** type ONLY.
        - DO NOT mix question types. If the quiz type is "Single Choice", ALL questions must be Single Choice.
        - DO NOT create True/False questions in Single Choice quizzes.
        - DO NOT create Multiple Choice questions in Single Choice quizzes.
        - DO NOT create Open Ended questions unless specifically requested.

        **Guidelines:**
        - You must generate exactly **{$data['max_questions']}** questions.
        - For Multiple Choice questions, ensure that there are exactly four answer options, with two options marked as `is_correct: true`.
        - For Single Choice questions, ensure that there are exactly four answer options, with one option marked as `is_correct: true`.
        - For True/False questions, ensure that there are exactly two answer options (True and False), with one option marked as `is_correct: true`.
        - For Open Ended questions, provide no answer options (empty answers array).
        - The correct_answer_key should match the correct answer's title value(s) for Multiple Choice, Single Choice, and True/False questions.
        - Ensure that each question is diverse and well-crafted, covering various relevant concepts.
        - **LANGUAGE COMPLIANCE**: Every single word in questions and answers MUST be in {$quizData['language']}. No exceptions.

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
                    ->persistent()
                    ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                    ->send();
                $this->halt();
            }

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
                    ->persistent()
                    ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                    ->send();
                $this->halt();
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
                    ->persistent()
                    ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                    ->send();
                $this->halt();
            }

            $quizResponse = Http::withToken($openAiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->timeout(90)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if ($quizResponse->failed()) {
                $error = $quizResponse->json()['error']['message'] ?? 'Unknown error occurred';
                Notification::make()->danger()->title(__('OpenAI Error'))->body($error)
                    ->persistent()
                    ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
                    ->send();
                $this->halt();
            }

            $quizText = $quizResponse['choices'][0]['message']['content'] ?? null;
        }

        Log::info("=== AI RESPONSE DEBUG START ===");
        Log::info("AI response received: " . ($quizText ? 'yes' : 'no'));
        Log::info("AI Type: " . getSetting()->ai_type);
        Log::info("Description: " . substr($description ?? '', 0, 200));
        Log::info("=== AI RESPONSE DEBUG END ===");

        if ($quizText) {
            $quizData = trim($quizText);
            Log::info("Raw AI response: " . substr($quizData, 0, 500));
            
            if (stripos($quizData, '```json') === 0) {
                $quizData = preg_replace('/^```json\s*|\s*```$/', '', $quizData);
                $quizData = trim($quizData);
            }
            $quizQuestions = json_decode($quizData, true);
            
            Log::info("Parsed questions count: " . (is_array($quizQuestions) ? count($quizQuestions) : 'not array'));
            Log::info("JSON decode error: " . json_last_error_msg());
            Log::info("Full AI response structure: " . json_encode($quizQuestions));

            $quiz = Quiz::create($input);
            Log::info("Quiz created with ID: " . $quiz->id);

            if (is_array($quizQuestions)) {
                $questionsCreated = 0;
                foreach ($quizQuestions as $index => $question) {
                    Log::info("Processing question " . (intval($index) + 1) . ": " . json_encode($question));
                    
                    // Normalize question structure to avoid duplicates from nested arrays
                    $questionsToProcess = [];
                    
                    // Check if this is a nested array of questions (AI sometimes returns nested structure)
                    if (is_array($question) && isset($question[0]) && is_array($question[0]) && isset($question[0]['question'])) {
                        Log::info("Found nested questions array, flattening " . count($question) . " questions");
                        foreach ($question as $nestedQuestion) {
                            if (isset($nestedQuestion['question'])) {
                                $questionsToProcess[] = $nestedQuestion;
                            }
                        }
                    } elseif (isset($question['question'])) {
                        // Single question
                        $questionsToProcess[] = $question;
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
                        continue;
                    }
                    
                    // Process all normalized questions
                    foreach ($questionsToProcess as $processedQuestion) {
                        $questionModel = Question::create([
                            'quiz_id' => $quiz->id,
                            'title' => $processedQuestion['question'],
                        ]);

                        // Check if answers array is not empty
                        if (is_array($processedQuestion['answers']) && !empty($processedQuestion['answers'])) {
                            foreach ($processedQuestion['answers'] as $answer) {
                                $isCorrect = false;
                                $correctKey = $processedQuestion['correct_answer_key'] ?? '';

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
                            Log::info("Question created successfully with " . count($processedQuestion['answers']) . " answers");
                            $questionsCreated++;
                        } else {
                            // For Open Ended questions or questions without answers
                            Log::info('Question created without answers (Open Ended): ' . $processedQuestion['question']);
                            $questionsCreated++;
                        }
                    }
                }
                
                Log::info("Total questions created in this loop: " . $questionsCreated);
                
               // Check if any questions were actually created
               $targetCount = (int)($data['max_questions'] ?? 0);
               $totalQuestions = Question::where('quiz_id', $quiz->id)->count();
               Log::info("Total questions created for quiz {$quiz->id}: {$totalQuestions} (target {$targetCount})");
               
               if ($totalQuestions == 0) {
                   Log::error("No questions were created despite AI response being processed");
               }

               // Ensure exact target by attempting backfill loops until satisfied (max 3 attempts)
               $attempt = 0;
               do {
               $actualDbCount = Question::where('quiz_id', $quiz->id)->count();
               $remaining = max(0, $targetCount - $actualDbCount);
               Log::info("Backfill calculation (attempt {$attempt}): target={$targetCount}, actual={$actualDbCount}, remaining={$remaining}");
               if ($remaining > 0) {
                   Log::warning("Backfill: attempting to generate remaining {$remaining} questions");

                   $quizData2 = [
                       'Difficulty' => Quiz::DIFF_LEVEL[$data['diff_level']],
                       'question_type' => Quiz::QUIZ_TYPE[$data['quiz_type']],
                       'language' => getAllLanguages()[$data['language']] ?? 'English'
                   ];
                   $prompt2 = <<<PROMPT

You are an expert in crafting engaging quizzes. Based on the quiz details provided, generate exactly {$remaining} additional questions. Output must be valid JSON array as described.

- Title: {$data['title']}
- Description: {$description}
- Number of Questions: {$remaining}
- Difficulty: {$quizData2['Difficulty']}
- Question Type: {$quizData2['question_type']}
- Language: {$quizData2['language']}

Follow the same formats previously specified for each question type. Create exactly {$remaining} items.
PROMPT;

                   $extraText = null;
                   if ($aiType == Quiz::GEMINI_AI) {
                       $geminiApiKey = getSetting()->gemini_api_key;
                       $model = getSetting()->gemini_ai_model;
                       if ($geminiApiKey) {
                           $geminiResponse2 = \Illuminate\Support\Facades\Http::withHeaders(['Content-Type'=>'application/json'])
                               ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$geminiApiKey}", [
                                   'contents' => [[ 'parts' => [['text' => $prompt2]] ]],
                               ]);
                           if ($geminiResponse2->ok()) {
                               $raw2 = $geminiResponse2->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                               $extraText = preg_replace('/^```(?:json)?|```$/im', '', $raw2);
                           }
                       }
                   } elseif ($aiType == Quiz::OPEN_AI) {
                       $key = getSetting()->open_api_key;
                       $openAiKey = (! empty($key)) ? $key : config('services.open_ai.open_api_key');
                       $model = getSetting()->open_ai_model;
                       if ($openAiKey) {
                           $resp2 = \Illuminate\Support\Facades\Http::withToken($openAiKey)
                               ->withHeaders(['Content-Type'=>'application/json'])
                               ->timeout(90)
                               ->post('https://api.openai.com/v1/chat/completions', [
                                   'model' => $model,
                                   'messages' => [[ 'role' => 'user', 'content' => $prompt2 ]],
                               ]);
                           if ($resp2->ok()) {
                               $extraText = $resp2['choices'][0]['message']['content'] ?? null;
                           }
                       }
                   }

                   if ($extraText) {
                       $extraData = trim($extraText);
                       if (stripos($extraData, '```json') === 0) {
                           $extraData = preg_replace('/^```json\s*|\s*```$/', '', $extraData);
                           $extraData = trim($extraData);
                       }
                       $moreQuestions = json_decode($extraData, true);
                       Log::info("Backfill parsed count: ".(is_array($moreQuestions)?count($moreQuestions):'not array'));
                       if (is_array($moreQuestions)) {
                           foreach ($moreQuestions as $mq) {
                               if (isset($mq['question'], $mq['answers'])) {
                                   $questionModel = Question::create([
                                       'quiz_id' => $quiz->id,
                                       'title' => $mq['question'],
                                   ]);
                                   if (is_array($mq['answers']) && !empty($mq['answers'])) {
                                       $correctKey = $mq['correct_answer_key'] ?? '';
                                       foreach ($mq['answers'] as $ans) {
                                           $isCorrect = false;
                                           if (is_array($correctKey)) {
                                               $isCorrect = in_array($ans['title'], $correctKey);
                                           } else {
                                               // For single choice, prefer AI's is_correct field over correct_answer_key matching
                                               if (is_array($ans) && array_key_exists('is_correct', $ans)) {
                                                   $provided = filter_var($ans['is_correct'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                                                   if (!is_null($provided)) {
                                                       $isCorrect = (bool)$provided;
                                                   } else {
                                                       $isCorrect = $ans['title'] === $correctKey;
                                                   }
                                               } else {
                                                   $isCorrect = $ans['title'] === $correctKey;
                                               }
                                           }
                                           Answer::create([
                                               'question_id' => $questionModel->id,
                                               'title' => $ans['title'],
                                               'is_correct' => $isCorrect,
                                           ]);
                                       }
                                   }
                                   // Count actual questions created
                                   $currentDbCount = Question::where('quiz_id', $quiz->id)->count();
                                   if ($currentDbCount >= $targetCount) break;
                               }
                           }
                       }
                   }
               }
               $attempt++;
               } while ($remaining > 0 && $attempt < 3);
            } else {
                Log::error('AI response is not a valid array: ' . $quizData);
                Log::error('JSON decode error: ' . json_last_error_msg());
            }

            return $quiz;
        }

        Log::error('No AI response received - quizText is empty or null');
        Log::error('AI Type: ' . getSetting()->ai_type);
        Log::error('Description length: ' . strlen($description ?? ''));
        Log::error('Description content: ' . substr($description ?? '', 0, 200));
        
        Notification::make()
            ->danger()
            ->title(__('messages.setting.something_went_wrong'))
            ->persistent()
            ->actions([NotificationAction::make('close')->label('Close')->button()->color('gray')->close()])
            ->send();
        $this->halt();
    }

    public function getTitle(): string
    {
        return __('messages.quiz.create_quiz');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('messages.quiz.quiz_created_success');
    }

    protected function getRedirectUrl(): string
    {
        $recordId = $this->record->id ?? null;
        // Append cache-buster and a hint to force full load of questions on first edit
        return $recordId
            ? $this->getResource()::getUrl('edit', ['record' => $recordId, 't' => time(), 'first' => 1, 'fresh' => 1])
            : $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        $create = parent::getFormActions()[0]
            ->label(__('messages.common.create'))
            ->extraAttributes([
                'x-data' => '{}',
                'x-on:click' => '$nextTick(()=>{ const s = $el.querySelector(".fi-btn-label") || $el.querySelector("span"); if (s) { s.textContent = "Please Wait"; } })',
                'wire:loading.attr' => 'disabled',
                'wire:target' => 'create',
                'wire:loading.class' => 'opacity-70 cursor-not-allowed',
            ]);

        return [
            $create,
            Action::make('cancel')->label(__('messages.common.cancel'))->color('gray')->url(QuizzesResource::getUrl('index')),
        ];
    }
}
