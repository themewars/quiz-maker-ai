<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Barryvdh\DomPDF\Facade\Pdf;
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
        
        // Set PDF options
        $pdf = Pdf::loadView('exports.quiz-pdf', [
            'quiz' => $quiz,
            'language' => $currentLanguage
        ]);

        // Set PDF options for better formatting
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans'
        ]);

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
