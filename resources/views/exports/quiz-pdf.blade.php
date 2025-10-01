<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} - Exam Export</title>
    @php
        $fontRegularPath = public_path('fonts/NotoSansDevanagari-Regular.ttf');
        $fontBoldPath = public_path('fonts/NotoSansDevanagari-Bold.ttf');
        $fontRegularData = file_exists($fontRegularPath) ? base64_encode(file_get_contents($fontRegularPath)) : null;
        $fontBoldData = file_exists($fontBoldPath) ? base64_encode(file_get_contents($fontBoldPath)) : null;
        $includeDescription = (string)request()->input('include_description', (string)(getUserSettings('include_description_default') ?? '1')) === '1';
        $includeAnswers = (string)request()->input('include_answers', (string)(getUserSettings('include_answers_default') ?? '1')) === '1';
        $markCorrect = (string)request()->input('mark_correct', (string)(getUserSettings('mark_correct_default') ?? '1')) === '1';
    @endphp
    <style>
        /* Embed Hindi-capable fonts so viewers without system fonts still see text */
        /* Only embed when font files actually exist to avoid invalid CSS */
        @if(!empty($fontRegularData))
        @font-face {
            font-family: 'Noto Sans Devanagari';
            src: url('data:font/ttf;base64,{!! $fontRegularData !!}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @endif
        @if(!empty($fontBoldData))
        @font-face {
            font-family: 'Noto Sans Devanagari';
            src: url('data:font/ttf;base64,{!! $fontBoldData !!}') format('truetype');
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }
        @endif

        body {
            font-family: 'Noto Sans Devanagari', 'Arial Unicode MS', 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            direction: ltr;
            unicode-bidi: embed;
        }

        /* Force font on all elements to avoid fallback boxes */
        * {
            font-family: 'Noto Sans Devanagari', 'Noto Sans Devanagari UI', 'Noto Serif Devanagari', 'Arial Unicode MS', 'DejaVu Sans', Arial, sans-serif !important;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 20px;
        }
        
        .quiz-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .quiz-meta {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .quiz-description {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            font-style: italic;
        }
        
        .quiz-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .quiz-details h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 16px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #34495e;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .questions-section {
            margin-top: 30px;
        }
        
        .questions-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 10px;
        }
        
        .question {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .question-number {
            font-weight: bold;
            color: #e74c3c;
            font-size: 14px;
        }
        
        .question-text {
            margin: 10px 0;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .answers {
            margin-left: 20px;
        }
        
        .answer {
            margin-bottom: 5px;
            padding: 5px 0;
        }
        
        .answer-option {
            font-weight: bold;
            color: #34495e;
        }
        
        .answer-text {
            margin-left: 10px;
        }
        
        .correct-answer {
            color: #27ae60;
            font-weight: bold;
        }
        
        .correct-answer::after {
            content: " ✓";
            color: #27ae60;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .question {
                page-break-inside: avoid;
            }
        }
        /* Watermark styles */
        .watermark-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
            width: 80%;
            text-align: center;
        }
        .watermark-container img {
            max-width: 240px;
            height: auto;
            display: block;
            margin: 0 auto 10px auto;
        }
        .watermark-text {
            font-size: 64px;
            font-weight: 700;
            color: #000;
            letter-spacing: 2px;
        }
        /* Ensure content appears above watermark */
        .content-wrapper { position: relative; z-index: 1; }
    </style>
</head>
<body>
    {{-- Live progress bar snippet is not for PDF; no change here --}}
    @if(!empty($watermarkEnabled))
        <div class="watermark-container">
            @if(!empty($watermarkLogo) && empty($whiteLabelEnabled))
                <img src="{{ $watermarkLogo }}" alt="{{ $watermarkText }}">
            @endif
            <div class="watermark-text">{{ $watermarkText }}</div>
        </div>
    @endif
    <div class="content-wrapper">
    <div class="header">
        <div class="quiz-title">{{ $quiz->title }}</div>
        <div class="quiz-meta">{{ __('messages.quiz.quiz_export') }} - {{ date('d/m/Y H:i') }}</div>
        <div class="quiz-meta">{{ __('messages.common.created_by') }}: {{ $quiz->user->name ?? 'N/A' }}</div>
    </div>

    @if($includeDescription && $quiz->quiz_description)
    <div class="quiz-description">
        <strong>{{ __('messages.quiz.description') }}:</strong><br>
        {{ $quiz->quiz_description }}
    </div>
    @endif

    <div class="quiz-details">
        <h3>{{ __('messages.quiz.quiz_details') }}</h3>
        <div class="detail-row">
            <div class="detail-label">{{ __('messages.quiz.category') }}:</div>
            <div class="detail-value">{{ $quiz->category ? $quiz->category->name : __('messages.common.n/a') }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">{{ __('messages.quiz.difficulty') }}:</div>
            <div class="detail-value">{{ \App\Models\Quiz::DIFF_LEVEL[$quiz->diff_level] ?? __('messages.common.n/a') }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">{{ __('messages.quiz.question_type') }}:</div>
            <div class="detail-value">{{ \App\Models\Quiz::QUIZ_TYPE[$quiz->quiz_type] ?? __('messages.common.n/a') }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">{{ __('messages.quiz.total_questions') }}:</div>
            <div class="detail-value">{{ $quiz->questions->count() }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">{{ __('messages.quiz.created_at') }}:</div>
            <div class="detail-value">{{ $quiz->created_at->format('d/m/Y H:i') }}</div>
        </div>
        @if($quiz->time_configuration)
        <div class="detail-row">
            <div class="detail-label">{{ __('messages.quiz.time_limit') }}:</div>
            <div class="detail-value">{{ $quiz->time }} {{ $quiz->time_type == 1 ? __('messages.quiz.minutes') : __('messages.quiz.seconds') }}</div>
        </div>
        @endif
    </div>

    <div class="questions-section">
        <div class="questions-title">{{ __('messages.quiz.questions') }}</div>
        
        @forelse($quiz->questions as $index => $question)
        <div class="question">
            <div class="question-number">{{ $index + 1 }}.</div>
            <div class="question-text">{{ $question->title }}</div>
            
            @if($includeAnswers && $question->answers->count() > 0)
            <div class="answers">
                @foreach($question->answers as $answerIndex => $answer)
                @php($isCorrect = $markCorrect ? $answer->is_correct : false)
                <div class="answer">
                    <span class="answer-option">{{ chr(65 + $answerIndex) }})</span>
                    <span class="answer-text {{ $isCorrect ? 'correct-answer' : '' }}">
                        {{ $answer->title }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="question">
            <div class="question-text">{{ __('messages.quiz.no_questions_found') }}</div>
        </div>
        @endforelse
    </div>

    <div class="footer">
        @unless(!empty($whiteLabelEnabled))
            <div>{{ __('messages.quiz.exported_from') }} ExamGenerator AI</div>
        @endunless
        <div>{{ __('messages.quiz.export_date') }}: {{ date('d/m/Y H:i:s') }}</div>
        <div>{{ __('messages.quiz.quiz_code') }}: {{ $quiz->unique_code }}</div>
    </div>
    </div>
</body>
</html>
