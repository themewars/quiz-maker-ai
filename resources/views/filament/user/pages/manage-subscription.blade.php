<section class="flex flex-col gap-y-8 py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="">
            <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                {{ __('messages.subscription.manage_subscription') }}
            </h1>
        </div>
        <div class="fi-ac gap-3 flex flex-wrap items-center justify-start shrink-0">
            <a href="{{ route('filament.user.pages.upgrade-subscription') }}"
                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action">
                <span class="fi-btn-label">{{ __('messages.subscription.upgrade_plan') }}</span>
            </a>
        </div>
    </div>
    @php($sub = getActiveSubscription())
    @if($sub && $sub->plan)
        @php($plan = $sub->plan)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Current Plan</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</div>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Valid till: <span class="font-medium text-gray-800 dark:text-gray-200">{{ optional($sub->ends_at)->format('d M Y') }}</span>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">
                    Max exams: <span class="font-semibold">{{ (int)($plan->no_of_quiz ?? 0) > 0 ? (int)$plan->no_of_quiz : 'Unlimited' }}</span>
                </div>
                <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">
                    Max questions per exam: <span class="font-semibold">{{ (int)($plan->max_questions_per_exam ?? 0) > 0 ? (int)$plan->max_questions_per_exam : 'Unlimited' }}</span>
                </div>
                <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">
                    Monthly question limit: <span class="font-semibold">{{ (int)($plan->max_questions_per_month ?? -1) >= 0 ? (int)$plan->max_questions_per_month : 'Unlimited' }}</span>
                </div>
                <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">
                    Max PDF pages: <span class="font-semibold">{{ (int)($plan->max_pdf_pages ?? 0) > 0 ? (int)$plan->max_pdf_pages : 'Unlimited' }}</span>
                </div>
                <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">
                    PDF Export: <span class="font-semibold">{{ $plan->export_pdf ? 'Enabled' : 'Disabled' }}</span>
                </div>
                <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">
                    Word Export: <span class="font-semibold">{{ $plan->export_word ? 'Enabled' : 'Disabled' }}</span>
                </div>
                <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm sm:col-span-2 lg:col-span-3">
                    Allowed question types:
                    @php($allowed = (array)($plan->allowed_question_types ?? []))
                    @php($map = [0=>'Multiple choice',1=>'Single choice',2=>'True/False',3=>'Open ended'])
                    <span class="font-semibold">
                        @if(empty($allowed))
                            All
                        @else
                            {{ implode(', ', array_map(function($key) use ($map){ return $map[$key] ?? $key; }, array_keys(array_flip($allowed)) )) }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    @endif
    <div>
        {{ $this->table }}
    </div>
</section>
