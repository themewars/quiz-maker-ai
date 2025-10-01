@extends('filament::page')

@section('content')
    @parent
    @php($quizId = $this->record->id ?? null)
    <div x-data="{
            total: 0,
            done: 0,
            status: 'idle',
            timer: null,
            quizId: {{ $quizId ?? 'null' }},
            start() {
                if (!this.quizId) return;
                this.timer = setInterval(async () => {
                    const res = await fetch(`/api/quizzes/${this.quizId}/progress`, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        this.total = data.total || 0;
                        this.done = data.done || 0;
                        this.status = data.status || 'idle';
                        if (this.status === 'completed') { clearInterval(this.timer); }
                    }
                }, 1500);
            },
        }" x-init="start()" class="mt-4">
        <template x-if="status !== 'idle'">
            <div class="p-3 rounded-md border border-gray-200 bg-white dark:bg-gray-900">
                <div class="text-sm mb-2">Generating questions: <span x-text="done"></span>/<span x-text="total"></span> (<span x-text="total ? Math.round((done/total)*100) : 0"></span>%)</div>
                <div class="w-full h-2 bg-gray-200 rounded">
                    <div class="h-2 bg-indigo-500 rounded" :style="`width: ${total ? (done/total*100) : 0}%`"></div>
                </div>
                <div class="text-xs mt-1" x-text="status"></div>
                <div class="mt-2">
                    <button type="button" class="fi-btn fi-btn-size-md fi-color-primary" @click="$wire.refreshQuestions()">Refresh list now</button>
                </div>
            </div>
        </template>
    </div>
@endsection


