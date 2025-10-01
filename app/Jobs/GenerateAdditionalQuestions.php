<?php

namespace App\Jobs;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateAdditionalQuestions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $quizId;
    protected int $additionalQuestions;
    protected string $description;
    protected array $meta;

    public function __construct(int $quizId, int $additionalQuestions, string $description, array $meta)
    {
        $this->quizId = $quizId;
        $this->additionalQuestions = $additionalQuestions;
        $this->description = $description;
        $this->meta = $meta; // ['title','difficulty','question_type','language','ai_type','open_ai_key','open_ai_model']
    }

    public function handle(): void
    {
        $quiz = Quiz::find($this->quizId);
        if (! $quiz) {
            Log::warning('GenerateAdditionalQuestions: Quiz not found: ' . $this->quizId);
            return;
        }

        $aiType = $this->meta['ai_type'] ?? Quiz::OPEN_AI;

        if ($aiType === Quiz::OPEN_AI) {
            $openAiKey = (string)($this->meta['open_ai_key'] ?? '');
            $model = (string)($this->meta['open_ai_model'] ?? 'gpt-4o-mini');
            if ($openAiKey === '') {
                Log::error('GenerateAdditionalQuestions: OpenAI key missing');
                return;
            }

            $allItems = [];
            $remainingTotal = $this->additionalQuestions;
            $maxPerRequest = 25;

            while ($remainingTotal > 0) {
                $thisBatch = min($maxPerRequest, $remainingTotal);
                $prompt = $this->buildPrompt($thisBatch);

                $resp = Http::withToken($openAiKey)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->connectTimeout(20)
                    ->timeout(180)
                    ->retry(3, 2000)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'temperature' => 0.7,
                        'max_tokens' => 12000,
                        'messages' => [[
                            'role' => 'user',
                            'content' => $prompt,
                        ]],
                    ]);

                if ($resp->failed()) {
                    Log::error('OpenAI chunk failed: ' . ($resp->json()['error']['message'] ?? 'unknown'));
                    break;
                }

                $chunkText = $resp['choices'][0]['message']['content'] ?? '';
                $chunkText = trim(preg_replace('/^```json\s*|\s*```$/', '', $chunkText));
                $chunkItems = json_decode($chunkText, true);
                if (!is_array($chunkItems)) {
                    Log::warning('Chunk decode failed in job: ' . json_last_error_msg());
                    break;
                }
                $allItems = array_merge($allItems, $chunkItems);
                $remainingTotal = $this->additionalQuestions - count($allItems);
            }

            if (count($allItems) > $this->additionalQuestions) {
                $allItems = array_slice($allItems, 0, $this->additionalQuestions);
            }

            $this->persistQuestions($quiz, $allItems);
        }
    }

    protected function buildPrompt(int $count): string
    {
        $title = $this->meta['title'] ?? '';
        $difficulty = $this->meta['difficulty'] ?? '';
        $qtype = $this->meta['question_type'] ?? '';
        $language = $this->meta['language'] ?? 'English';
        $desc = $this->description;

        return <<<PROMPT
You are an expert in crafting engaging quizzes. Generate exactly {$count} additional questions according to the specified question type.

STRICT OUTPUT REQUIREMENTS:
- Output MUST be a JSON array with LENGTH exactly {$count}. Do not exceed or go under.
- Do NOT include any surrounding prose, markdown, headings, or keys other than the array itself.

**Quiz Details:**
- **Title**: {$title}
- **Description**: {$desc}
- **Number of Additional Questions**: {$count}
- **Difficulty**: {$difficulty}
- **Question Type**: {$qtype}
- **Language**: {$language}

[Return ONLY the JSON array as described.]
PROMPT;
    }

    protected function persistQuestions(Quiz $quiz, array $items): void
    {
        $added = 0;
        foreach ($items as $question) {
            if (!isset($question['question'])) { continue; }
            $q = Question::create([
                'quiz_id' => $quiz->id,
                'title' => (string)$question['question'],
            ]);
            $answers = is_array($question['answers'] ?? null) ? $question['answers'] : [];
            $correctKey = $question['correct_answer_key'] ?? '';

            // If API didn't return answers, synthesize based on question type where possible
            if (empty($answers)) {
                $qtype = strtolower((string)($this->meta['question_type'] ?? ''));
                if (strpos($qtype, 'true') !== false && strpos($qtype, 'false') !== false) {
                    // True/False: create two options
                    $trueCorrect = (is_string($correctKey) && strtolower($correctKey) === 'true');
                    $falseCorrect = (is_string($correctKey) && strtolower($correctKey) === 'false');
                    Answer::create(['question_id' => $q->id, 'title' => 'True', 'is_correct' => $trueCorrect]);
                    Answer::create(['question_id' => $q->id, 'title' => 'False', 'is_correct' => $falseCorrect]);
                }
            } else {
                foreach ($answers as $ans) {
                    $title = is_array($ans) ? ($ans['title'] ?? '') : '';
                    $isCorrect = false;
                    if (is_array($correctKey)) { $isCorrect = in_array($title, $correctKey); }
                    else { $isCorrect = $title === $correctKey; }
                    // if API already provided is_correct, prefer it unless empty
                    if (is_array($ans) && array_key_exists('is_correct', $ans)) {
                        $provided = filter_var($ans['is_correct'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if (!is_null($provided)) { $isCorrect = (bool)$provided; }
                    }
                    Answer::create([
                        'question_id' => $q->id,
                        'title' => $title,
                        'is_correct' => $isCorrect,
                    ]);
                }
            }
            $added++;
        }
        Log::info("GenerateAdditionalQuestions job added {$added} questions to quiz {$quiz->id}");
    }
}


