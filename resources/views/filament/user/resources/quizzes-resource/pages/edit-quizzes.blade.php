@extends('filament::page')

@section('content')
    @parent
    @php($quizId = $this->record->id ?? null)
    <div x-data="{ quizId: {{ $quizId ?? 'null' }}, timer: null, lastDone: -1, start(){ if(!this.quizId) return; this.timer = setInterval(async()=>{ const res = await fetch(`/api/quizzes/${this.quizId}/progress`, { headers: { 'Accept': 'application/json' } }); if(res.ok){ const data = await res.json(); const done = data?.done ?? 0; const status = data?.status ?? 'idle'; if(done !== this.lastDone && $wire && $wire.refreshQuestions){ this.lastDone = done; $wire.refreshQuestions(); } if(status === 'completed'){ clearInterval(this.timer); } } }, 1500); } }" x-init="start()"></div>
@endsection
