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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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

    protected function handleRecordCreation(array $data): Model
    {
        $userId = Auth::id();
        $activeTab = getTabType();

        $descriptionFields = [
            Quiz::TEXT_TYPE => $data['quiz_description_text'],
            Quiz::SUBJECT_TYPE => $data['quiz_description_sub'],
            Quiz::URL_TYPE => $data['quiz_description_url'],
        ];

        $description = $descriptionFields[$activeTab] ?? null;

        $input = [
            'user_id' => $userId,
            'title' => $data['title'],
            'category_id' => $data['category_id'],
            'quiz_description' => $description,
            'type' => $activeTab,
            'status' => 1,
            'quiz_type' => $data['quiz_type'] ?? 0,
            'max_questions' => $data['max_questions'] ?? 0,
            'diff_level' => $data['diff_level'] ?? 0,
            'unique_code' => generateUniqueCode(),
            'language' => $data['language'] ?? 'en',
            'time_configuration' => $data['time_configuration'] ?? 0,
            'time' => $data['time'] ?? 0,
            'time_type' => $data['time_type'] ?? null,
            'quiz_expiry_date' => $data['quiz_expiry_date'] ?? null,
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
                    $filePath = $file->store('temp-file', 'public');
                    $fileUrl = Storage::disk('public')->url($filePath);
                    $extension = pathinfo($fileUrl, PATHINFO_EXTENSION);

                    if ($extension === 'pdf') {
                        $description = pdfToText($fileUrl);
                    } elseif ($extension === 'docx') {
                        $description = docxToText($fileUrl);
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

    **Instructions:**

    1. **Language Requirement**: Write all quiz questions and answers in {$data['language']}.
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
        - Use the following format with exactly two options. Mark one option as `is_correct: true` and the other as `is_correct: false`:

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
                    }
                ],
                "correct_answer_key": "Answer Option 2"
            }
        ]

    **Guidelines:**
    - You must generate exactly **{$data['max_questions']}** questions.
    - For Multiple Choice questions, ensure that there are exactly four answer options, with two options marked as `is_correct: true`.
    - For Single Choice questions, ensure that there are exactly two answer options, with one option marked as `is_correct: true`.
    - The correct_answer_key should match the correct answer's title value(s) for Multiple Choice and Single Choice questions.
    - Ensure that each question is diverse and well-crafted, covering various relevant concepts.

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
                Notification::make()->danger()->title(__('OpenAI Error'))->body($error)->send();
                $this->halt();
            }

            $quizText = $quizResponse['choices'][0]['message']['content'] ?? null;
        }

        if ($quizText) {
            $quizData = trim($quizText);
            if (stripos($quizData, '```json') === 0) {
                $quizData = preg_replace('/^```json\s*|\s*```$/', '', $quizData);
                $quizData = trim($quizData);
            }
            $quizQuestions = json_decode($quizData, true);

            $quiz = Quiz::create($input);

            if (is_array($quizQuestions)) {
                foreach ($quizQuestions as $question) {
                    if (isset($question['question'], $question['answers'])) {
                        $questionModel = Question::create([
                            'quiz_id' => $quiz->id,
                            'title' => $question['question'],
                        ]);

                        foreach ($question['answers'] as $answer) {
                            $isCorrect = false;
                            $correctKey = $question['correct_answer_key'];

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
                    }
                }
            }

            return $quiz;
        }

        Notification::make()
            ->danger()
            ->title(__('messages.setting.something_went_wrong'))
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

        return $recordId ? $this->getResource()::getUrl('edit', ['record' => $recordId]) : $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            parent::getFormActions()[0],
            Action::make('cancel')->label(__('messages.common.cancel'))->color('gray')->url(QuizzesResource::getUrl('index')),
        ];
    }
}
