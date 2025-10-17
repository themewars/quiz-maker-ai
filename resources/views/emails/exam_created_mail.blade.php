@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img src="{{ asset('images/logo-ai.png') }}" class="logo" style="height:auto!important;width:auto!important;object-fit:cover"
                alt="ExamGenerator.AI">
        @endcomponent
    @endslot
    
    <h2>Hello {{ $user->name ?? 'User' }}!</h2>
    
    <p>Great news! Your exam has been created successfully. Here are the details:</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="color: #333; margin-top: 0;">Exam Details</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 10px;">
                <strong>Exam Title:</strong> {{ $exam->title ?? 'Untitled Exam' }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Description:</strong> {{ strlen($exam->quiz_description ?? '') > 100 ? substr($exam->quiz_description, 0, 100) . '...' : ($exam->quiz_description ?? 'No description provided') }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Questions:</strong> {{ $exam->max_questions ?? 0 }} questions
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Difficulty:</strong> 
                @if(($exam->diff_level ?? 0) == 0)
                    Easy
                @elseif(($exam->diff_level ?? 0) == 1)
                    Medium
                @else
                    Hard
                @endif
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Language:</strong> {{ $exam->language ?? 'English' }}
            </li>
            @if($exam->time_configuration ?? false)
                <li style="margin-bottom: 10px;">
                    <strong>Time Limit:</strong> {{ $exam->time ?? 0 }} 
                    @if(($exam->time_type ?? 0) == 1)
                        minutes per question
                    @else
                        minutes total
                    @endif
                </li>
            @endif
            @if($exam->quiz_expiry_date ?? false)
                <li style="margin-bottom: 10px;">
                    <strong>Expiry Date:</strong> {{ $exam->quiz_expiry_date ? date('M d, Y', strtotime($exam->quiz_expiry_date)) : 'No expiry date' }}
                </li>
            @endif
        </ul>
    </div>
    
    <p style="margin: 20px 0;">
        You can now share this exam with participants or take it yourself. Click the button below to access your exam:
    </p>
    
    @component('mail::button', ['url' => $examUrl ?? '#', 'color' => 'primary'])
        View Your Exam
    @endcomponent
    
    <p style="margin-top: 20px;">
        <strong>Exam URL:</strong><br>
        <a href="{{ $examUrl ?? '#' }}" style="color: #007bff; word-break: break-all;">{{ $examUrl ?? 'URL not available' }}</a>
    </p>
    
    <p style="margin-top: 20px;">Thank you for your attention to this matter!</p>
    <p>Best regards,</p>
    <p>ExamGenerator.AI</p>
    
    @slot('footer')
        @component('mail::footer')
            <h6>© {{ date('Y') }} ExamGenerator.AI.</h6>
        @endcomponent
    @endslot
@endcomponent