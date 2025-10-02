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
            start(){ 
                if(!this.quizId) return; 
                this.timer = setInterval(async()=>{ 
                    try {
                        const res = await fetch(`/api/quizzes/${this.quizId}/progress`, { headers: { 'Accept': 'application/json' } }); 
                        if(res.ok){ 
                            const data = await res.json(); 
                            this.done = data?.done ?? 0; 
                            const status = data?.status ?? 'idle'; 
                            if(status === 'completed'){ 
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
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                    <div>
                        <div class="font-medium text-blue-900">Generating Questions</div>
                        <div class="text-sm text-blue-700">
                            <span x-text="done"></span>/<span x-text="total"></span> questions completed
                        </div>
                    </div>
                </div>
                <div class="mt-2 w-full bg-blue-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                         :style="`width: ${total > 0 ? (done/total*100) : 0}%`"></div>
                </div>
            </div>
        </div>
    @endif
@endsection
