@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img src="{{ getAppLogo() }}" class="logo" style="height:auto!important;width:auto!important;object-fit:cover"
                alt="{{ getAppName() }}">
        @endcomponent
    @endslot
    
    <h2>{{ __('messages.mail.hello') }} {{ $user->name }}!</h2>
    
    <p>Great news! Your exam has been created successfully. Here are the details:</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="color: #333; margin-top: 0;">Exam Details</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 10px;">
                <strong>Exam Title:</strong> {{ $exam->title }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Description:</strong> {{ Str::limit($exam->quiz_description, 100) }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Questions:</strong> {{ $exam->max_questions }} questions
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Difficulty:</strong> {{ $exam->diff_level == 0 ? 'Easy' : ($exam->diff_level == 1 ? 'Medium' : 'Hard') }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Language:</strong> {{ $exam->language }}
            </li>
            @if($exam->time_configuration)
                <li style="margin-bottom: 10px;">
                    <strong>Time Limit:</strong> {{ $exam->time }} {{ $exam->time_type == 1 ? 'minutes per question' : 'minutes total' }}
                </li>
            @endif
            @if($exam->quiz_expiry_date)
                <li style="margin-bottom: 10px;">
                    <strong>Expiry Date:</strong> {{ \Carbon\Carbon::parse($exam->quiz_expiry_date)->format('M d, Y') }}
                </li>
            @endif
        </ul>
    </div>
    
    <p style="margin: 20px 0;">
        You can now share this exam with participants or take it yourself. Click the button below to access your exam:
    </p>
    
    @component('mail::button', ['url' => $examUrl, 'color' => 'primary'])
        View Your Exam
    @endcomponent
    
    <p style="margin-top: 20px;">
        <strong>Exam URL:</strong><br>
        <a href="{{ $examUrl }}" style="color: #007bff; word-break: break-all;">{{ $examUrl }}</a>
    </p>
    
    <p style="margin-top: 20px;">{{ __('messages.mail.thank_you_or_your_attention') }}</p>
    <p>{{ __('messages.mail.best_regards') }}</p>
    <p>{{ getAppName() }}</p>
    
    @slot('footer')
        @component('mail::footer')
            <h6>© {{ date('Y') }} {{ getAppName() }}.</h6>
        @endcomponent
    @endslot
@endcomponent
