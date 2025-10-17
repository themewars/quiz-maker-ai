@component('mail::message')
# Hello {{ $user->name ?? 'User' }}!

Great news! Your exam has been created successfully. Here are the details:

## Exam Details

**Exam Title:** {{ $exam->title ?? 'Untitled Exam' }}

**Description:** {{ strlen($exam->quiz_description ?? '') > 100 ? substr($exam->quiz_description, 0, 100) . '...' : ($exam->quiz_description ?? 'No description provided') }}

**Questions:** {{ $exam->max_questions ?? 0 }} questions

**Difficulty:** 
@if(($exam->diff_level ?? 0) == 0)
Easy
@elseif(($exam->diff_level ?? 0) == 1)
Medium
@else
Hard
@endif

**Language:** {{ $exam->language ?? 'English' }}

@if($exam->time_configuration ?? false)
**Time Limit:** {{ $exam->time ?? 0 }} 
@if(($exam->time_type ?? 0) == 1)
minutes per question
@else
minutes total
@endif
@endif

@if($exam->quiz_expiry_date ?? false)
**Expiry Date:** {{ $exam->quiz_expiry_date ? date('M d, Y', strtotime($exam->quiz_expiry_date)) : 'No expiry date' }}
@endif

---

You can now share this exam with participants or take it yourself. Click the button below to access your exam:

@component('mail::button', ['url' => $examUrl ?? '#'])
View Your Exam
@endcomponent

**Exam URL:** {{ $examUrl ?? 'URL not available' }}

Thank you for your attention to this matter!

Best regards,  
ExamGenerator.AI

@endcomponent