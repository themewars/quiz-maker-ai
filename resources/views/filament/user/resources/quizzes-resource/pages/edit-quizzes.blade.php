@extends('filament::page')

@section('content')
    @parent
    @php($quizId = $this->record->id ?? null)
    @php($isGenerating = session('generating_questions', false))
    
    @if($isGenerating)
        <div x-data="{ 
            quizId: {{ $quizId ?? 'null' }}, 
            timer: null, 
            start(){ 
                if(!this.quizId) return; 
                this.timer = setInterval(async()=>{ 
                    try {
                        const res = await fetch(`/api/quizzes/${this.quizId}/progress`, { headers: { 'Accept': 'application/json' } }); 
                        if(res.ok){ 
                            const data = await res.json(); 
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
        }" x-init="start()"></div>
    @endif
@endsection
