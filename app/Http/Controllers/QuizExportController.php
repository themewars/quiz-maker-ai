<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizExportController extends Controller
{
    public function exportToPdf(Request $request, Quiz $quiz)
    {
        // Check if user owns this quiz
        if ($quiz->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to quiz');
        }

        // Load quiz with questions and answers
        $quiz->load(['questions.answers', 'category', 'user']);

        // Get current language
        $currentLanguage = session('language', 'en');
        
        // Try Snappy PDF first (better Hindi support)
        try {
            $html = view('exports.quiz-pdf', [
                'quiz' => $quiz,
                'language' => $currentLanguage
            ])->render();

            $pdf = SnappyPdf::loadHTML($html)
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('encoding', 'UTF-8')
                ->setOption('margin-top', 0)
                ->setOption('margin-right', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('disable-smart-shrinking', true)
                ->setOption('print-media-type', true)
                ->setOption('dpi', 300)
                ->setOption('image-quality', 100)
                ->setOption('enable-local-file-access', true)
                ->setOption('load-error-handling', 'ignore')
                ->setOption('load-media-error-handling', 'ignore')
                ->setOption('javascript-delay', 1000)
                ->setOption('no-stop-slow-scripts', true)
                ->setOption('enable-javascript', true)
                ->setOption('debug-javascript', false)
                ->setOption('no-pdf-compression', true)
                ->setOption('lowquality', false)
                ->setOption('grayscale', false)
                ->setOption('disable-external-links', false)
                ->setOption('disable-internal-links', false)
                ->setOption('zoom', 1.0);

        } catch (\Exception $e) {
            // Fallback to DomPDF if Snappy fails
            $pdf = Pdf::loadView('exports.quiz-pdf', [
                'quiz' => $quiz,
                'language' => $currentLanguage
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isJavascriptEnabled' => false,
                'isFontSubsettingEnabled' => true,
                'defaultMediaType' => 'print',
                'fontHeightRatio' => 1.1,
                'dpi' => 150,
                'fontDir' => storage_path('fonts/'),
                'fontCache' => storage_path('fonts/'),
                'tempDir' => sys_get_temp_dir(),
                'chroot' => public_path(),
                'logOutputFile' => storage_path('logs/dompdf.log'),
            ]);
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