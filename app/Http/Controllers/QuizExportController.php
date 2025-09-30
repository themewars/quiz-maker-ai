<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizExportController extends Controller
{
    public function exportOptions(Request $request, Quiz $quiz)
    {
        if ($quiz->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to quiz');
        }

        $quiz->load(['questions.answers', 'category', 'user']);

        return view('exports.options', [
            'quiz' => $quiz,
        ]);
    }

    public function exportToPdf(Request $request, Quiz $quiz)
    {
        // Check if user owns this quiz
        if ($quiz->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to quiz');
        }

        // Load quiz with questions and answers
        $quiz->load(['questions.answers', 'category', 'user']);

        // Get current language
        $currentLanguage = session('language', getUserSettings('default_language') ?? 'en');

        // Watermark: enabled if user's active plan has watermark flag
        $subscription = getActiveSubscription();
        $watermarkEnabled = (bool)($subscription && $subscription->plan ? ($subscription->plan->watermark ?? false) : false);
        $watermarkText = getAppName();
        $watermarkLogo = getAppLogo();
        
        // Try Chrome (Browsershot) first – best Indic shaping support
        try {
            $html = view('exports.quiz-pdf', [
                'quiz' => $quiz,
                'language' => $currentLanguage,
                'watermarkEnabled' => $watermarkEnabled,
                'watermarkText' => $watermarkText,
                'watermarkLogo' => $watermarkLogo,
            ])->render();

            $tmpPath = storage_path('app/tmp');
            if (! is_dir($tmpPath)) {
                @mkdir($tmpPath, 0775, true);
            }
            $filePath = $tmpPath . '/quiz_' . $quiz->id . '_' . date('Ymd_His') . '.pdf';

            $chromePath = env('BROWSERSHOT_CHROME_PATH');
            $paper = strtoupper($request->input('paper', getUserSettings('default_paper') ?? 'A4'));
            $orientation = strtolower($request->input('orientation', getUserSettings('default_orientation') ?? 'portrait')) === 'landscape' ? 'landscape' : 'portrait';
            $paper = strtoupper($request->input('paper', getUserSettings('default_paper') ?? 'A4'));
            $orientation = strtolower($request->input('orientation', getUserSettings('default_orientation') ?? 'portrait')) === 'landscape' ? 'landscape' : 'portrait';
            $paper = strtoupper($request->input('paper', getUserSettings('default_paper') ?? 'A4'));
            $orientation = strtolower($request->input('orientation', getUserSettings('default_orientation') ?? 'portrait')) === 'landscape' ? 'landscape' : 'portrait';

            $b = Browsershot::html($html)
                ->format($paper)
                ->landscape($orientation === 'landscape')
                ->margins(0, 0, 0, 0)
                ->showBackground()
                ->emulateMedia('print')
                ->waitUntilNetworkIdle();
            $args = trim((string) env('BROWSERSHOT_CHROME_ARGS', ''));
            if ($args !== '') {
                $b->addChromiumArguments(explode(' ', $args));
            } else {
                $b->noSandbox();
            }
            if (! empty($chromePath)) {
                $b->setChromePath($chromePath);
            }
            $b->savePdf($filePath);

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Browsershot PDF generation failed: '.$e->getMessage());
            // Fallback 1: Snappy/wkhtmltopdf (good unicode, limited shaping)
            try {
                $html = view('exports.quiz-pdf', [
                    'quiz' => $quiz,
                    'language' => $currentLanguage,
                    'watermarkEnabled' => $watermarkEnabled,
                    'watermarkText' => $watermarkText,
                    'watermarkLogo' => $watermarkLogo,
                ])->render();

                $pdf = SnappyPdf::loadHTML($html)
                    ->setPaper($paper)
                    ->setOrientation($orientation)
                    ->setOption('encoding', 'UTF-8')
                    ->setOption('margin-top', 0)
                    ->setOption('margin-right', 0)
                    ->setOption('margin-bottom', 0)
                    ->setOption('margin-left', 0)
                    ->setOption('print-media-type', true)
                    ->setOption('dpi', 300)
                    ->setOption('enable-local-file-access', true);

                $filename = 'quiz_' . $quiz->id . '_' . date('Y-m-d_H-i-s') . '.pdf';
                return $pdf->download($filename);
            } catch (\Exception $e2) {
                Log::error('Snappy PDF generation failed: '.$e2->getMessage());
                // Fallback 2: DomPDF as last resort
                $pdf = Pdf::loadView('exports.quiz-pdf', [
                    'quiz' => $quiz,
                    'language' => $currentLanguage,
                    'watermarkEnabled' => $watermarkEnabled,
                    'watermarkText' => $watermarkText,
                    'watermarkLogo' => $watermarkLogo,
                ]);

                $pdf->setPaper($paper, $orientation);
                $pdf->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                ]);
                Log::warning('Using DomPDF fallback for quiz export; Indic shaping may be limited.');
            }
        }

        // Generate filename
        $filename = 'quiz_' . $quiz->id . '_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportToWord(Request $request, Quiz $quiz)
    {
        // Check if user owns this quiz
        if ($quiz->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to quiz');
        }

        // Load quiz with questions and answers
        $quiz->load(['questions.answers', 'category', 'user']);

        // Get current language
        $currentLanguage = session('language', 'en');

        // Create Word document
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('QuizWhiz AI');
        $properties->setTitle($quiz->title);
        $properties->setDescription('Quiz Export - ' . $quiz->title);

        // Add section
        $section = $phpWord->addSection();

        // Add title
        $section->addText($quiz->title, ['bold' => true, 'size' => 16]);
        $section->addTextBreak();

        // Add description if exists
        if ($quiz->quiz_description) {
            $section->addText('Description:', ['bold' => true]);
            $section->addText($quiz->quiz_description);
            $section->addTextBreak();
        }

        // Add quiz details
        $section->addText('Quiz Details:', ['bold' => true]);
        $section->addText('Category: ' . ($quiz->category ? $quiz->category->name : 'N/A'));
        $section->addText('Difficulty: ' . (Quiz::DIFF_LEVEL[$quiz->diff_level] ?? 'N/A'));
        $section->addText('Question Type: ' . (Quiz::QUIZ_TYPE[$quiz->quiz_type] ?? 'N/A'));
        $section->addText('Total Questions: ' . $quiz->questions->count());
        $section->addTextBreak();

        // Add questions
        $section->addText('Questions:', ['bold' => true, 'size' => 14]);
        $section->addTextBreak();

        foreach ($quiz->questions as $index => $question) {
            $section->addText(($index + 1) . '. ' . $question->title, ['bold' => true]);
            
            if ($question->answers->count() > 0) {
                $section->addText('Options:', ['bold' => true]);
                foreach ($question->answers as $answerIndex => $answer) {
                    $optionText = chr(65 + $answerIndex) . ') ' . $answer->title;
                    if ($answer->is_correct) {
                        $optionText .= ' ✓ (Correct)';
                    }
                    $section->addText($optionText);
                }
            }
            $section->addTextBreak();
        }

        // Generate filename
        $filename = 'quiz_' . $quiz->id . '_' . date('Y-m-d_H-i-s') . '.docx';

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'quiz_export_');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}