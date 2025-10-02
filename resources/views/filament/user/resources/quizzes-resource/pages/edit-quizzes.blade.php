@extends('filament-panels::page')

@section('content')
    @php($quizId = $this->record->id ?? null)
    @php($isGenerating = session('generating_questions', false))
    @php($generatingCount = session('generating_count', 0))
    
    {{-- Question Count Notice --}}
    @php
        $currentQuestionCount = \App\Models\Question::where('quiz_id', $this->record->id ?? 0)->count();
        $subscription = getActiveSubscription();
        $maxQuestions = 0;
        if ($subscription && $subscription->plan) {
            if (is_numeric($subscription->plan->max_questions_per_exam)) {
                $maxQuestions = (int)$subscription->plan->max_questions_per_exam;
            } elseif (is_array($subscription->plan->max_questions_per_exam) && isset($subscription->plan->max_questions_per_exam[0]) && is_numeric($subscription->plan->max_questions_per_exam[0])) {
                $maxQuestions = (int)$subscription->plan->max_questions_per_exam[0];
            }
        }
    @endphp

    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-green-900">Questions in this Exam</h3>
                    <p class="text-sm text-green-700">
                        Total Questions: <span class="font-semibold">{{ $currentQuestionCount }}</span>
                        @if($maxQuestions > 0)
                            | Your Plan Limit: <span class="font-semibold">{{ $maxQuestions }} questions</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="text-right">
                @if($maxQuestions > 0)
                    @php
                        $remaining = $maxQuestions - $currentQuestionCount;
                        $percentage = ($currentQuestionCount / $maxQuestions) * 100;
                    @endphp
                    <div class="text-2xl font-bold text-green-600">{{ $currentQuestionCount }}</div>
                    <div class="text-xs text-green-500">of {{ $maxQuestions }}</div>
                    <div class="w-16 bg-green-200 rounded-full h-2 mt-1">
                        <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                             style="width: {{ min($percentage, 100) }}%"></div>
                    </div>
                    @if($remaining > 0)
                        <div class="text-xs text-green-600 mt-1">{{ $remaining }} remaining</div>
                    @else
                        <div class="text-xs text-orange-600 mt-1">Limit reached</div>
                    @endif
                @else
                    <div class="text-2xl font-bold text-green-600">{{ $currentQuestionCount }}</div>
                    <div class="text-xs text-green-500">questions</div>
                @endif
            </div>
        </div>
    </div>
    
    @if($isGenerating)
        <div x-data="{ 
            quizId: {{ $quizId ?? 'null' }}, 
            timer: null, 
            done: 0,
            total: {{ $generatingCount }},
            status: 'running',
            start(){ 
                if(!this.quizId) return; 
                this.timer = setInterval(async()=>{ 
                    try {
                        const res = await fetch(`/api/quizzes/${this.quizId}/progress`, { headers: { 'Accept': 'application/json' } }); 
                        if(res.ok){ 
                            const data = await res.json(); 
                            this.done = data?.done ?? 0; 
                            this.status = data?.status ?? 'running'; 
                            if(this.status === 'completed'){ 
                                clearInterval(this.timer); 
                                // Scroll to bottom and show success message
                                setTimeout(() => {
                                    // Scroll to bottom of page
                                    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                                    // Show success notification
                                    window.dispatchEvent(new CustomEvent('questions-added', { 
                                        detail: { count: this.total } 
                                    }));
                                    // Refresh the page to show new questions
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                }, 500);
                            } 
                        }
                    } catch(e) {
                        console.error('Progress polling error:', e);
                    }
                }, 1000); 
            }
        }" x-init="start()" class="mb-4">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="animate-spin rounded-full h-6 w-6 border-2 border-blue-600 border-t-transparent"></div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-blue-900">Generating Questions</h3>
                        <p class="text-sm text-blue-700">
                            <span x-text="done"></span> of <span x-text="total"></span> questions completed
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600" x-text="total > 0 ? Math.round((done/total)*100) : 0"></div>
                        <div class="text-xs text-blue-500">% Complete</div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-blue-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-3 rounded-full transition-all duration-500 ease-out" 
                             :style="`width: ${total > 0 ? (done/total*100) : 0}%`"></div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-blue-600 text-center">
                    <span x-show="status === 'running'">AI is working on your questions...</span>
                    <span x-show="status === 'saving'">Saving questions to database...</span>
                    <span x-show="status === 'completed'">Complete! Refreshing page...</span>
                </div>
            </div>
        </div>
    @endif


    {{-- Success Message for Added Questions --}}
    <div x-data="{ showSuccess: false, addedCount: 0 }" 
         x-init="
            window.addEventListener('questions-added', (e) => {
                this.addedCount = e.detail.count;
                this.showSuccess = true;
                setTimeout(() => this.showSuccess = false, 5000);
            });
         "
         x-show="showSuccess" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="fixed bottom-4 right-4 z-50">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-sm">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold">Questions Added Successfully!</h4>
                    <p class="text-sm opacity-90" x-text="`${addedCount} new questions added to your exam`"></p>
                </div>
            </div>
        </div>
    </div>
@endsection
