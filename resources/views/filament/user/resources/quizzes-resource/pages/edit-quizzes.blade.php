@extends('filament::page')

@section('content')
    @parent
    @php($quizId = $this->record->id ?? null)
    @php($isGenerating = session('generating_questions', false))
    @php($generatingCount = session('generating_count', 0))
    
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
                                // Refresh page to show all questions
                                window.location.reload();
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
@endsection
