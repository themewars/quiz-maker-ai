@extends('filament::page')

@section('content')
    @parent
    @php($quizId = $this->record->id ?? null)
    <div x-data="{ quizId: {{ $quizId ?? 'null' }}, timer: null, start(){ if(!this.quizId) return; this.timer = setInterval(async()=>{ const res = await fetch(`/api/quizzes/${this.quizId}/progress`, { headers: { 'Accept': 'application/json' } }); if(res.ok){ const data = await res.json(); if(data && data.status === 'completed'){ clearInterval(this.timer); if($wire && $wire.refreshQuestions){ $wire.refreshQuestions(); } } } }, 1500); } }" x-init="start()"></div>
@endsection
