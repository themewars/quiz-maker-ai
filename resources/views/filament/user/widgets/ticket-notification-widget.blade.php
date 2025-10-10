@php
    $data = $this->getViewData();
    $ticketsWithNewReplies = $data['ticketsWithNewReplies'];
@endphp

@if($ticketsWithNewReplies > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-blue-800">
                    You have {{ $ticketsWithNewReplies }} new admin reply{{ $ticketsWithNewReplies > 1 ? 'ies' : '' }}!
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Admin has replied to your support ticket{{ $ticketsWithNewReplies > 1 ? 's' : '' }}. Please check your tickets for updates.</p>
                </div>
                <div class="mt-3">
                    <div class="-mx-2 -my-1.5 flex">
                        <a href="{{ route('filament.user.resources.tickets.index') }}" 
                           class="bg-blue-50 px-2 py-1.5 rounded-md text-sm font-medium text-blue-800 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-50 focus:ring-blue-600">
                            View Support Tickets
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
