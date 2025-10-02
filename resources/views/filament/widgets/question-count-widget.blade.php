<x-filament::widget>
    <x-filament::card>
        @php
            $currentQuestionCount = $currentQuestionCount ?? 0;
            $maxQuestions = $maxQuestions ?? 0;
        @endphp

        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg shadow-sm">
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
                            $percentage = ($maxQuestions > 0) ? ($currentQuestionCount / $maxQuestions) * 100 : 0;
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
    </x-filament::card>
</x-filament::widget>
