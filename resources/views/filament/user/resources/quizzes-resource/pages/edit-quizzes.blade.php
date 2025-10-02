@extends('filament::page')

@section('content')
    @parent
    @php($quizId = $this->record->id ?? null)
    <div x-data="{ 
        quizId: {{ $quizId ?? 'null' }}, 
        timer: null, 
        lastDone: -1, 
        start(){ 
            if(!this.quizId) return; 
            console.log('Starting progress polling for quiz:', this.quizId);
            this.timer = setInterval(async()=>{ 
                try {
                    const res = await fetch(`/api/quizzes/${this.quizId}/progress`, { headers: { 'Accept': 'application/json' } }); 
                    if(res.ok){ 
                        const data = await res.json(); 
                        const done = data?.done ?? 0; 
                        const status = data?.status ?? 'idle'; 
                        console.log('Progress update:', { done, status, lastDone: this.lastDone });
                        if(done !== this.lastDone && $wire && $wire.refreshQuestions){ 
                            console.log('Refreshing questions...');
                            this.lastDone = done; 
                            $wire.refreshQuestions(); 
                        } 
                        if(status === 'completed'){ 
                            console.log('Generation completed, stopping timer');
                            clearInterval(this.timer); 
                        } 
                    } else {
                        console.error('Progress API failed:', res.status);
                    }
                } catch(e) {
                    console.error('Progress polling error:', e);
                }
            }, 1500); 
        } 
    }" x-init="start()"></div>
@endsection
