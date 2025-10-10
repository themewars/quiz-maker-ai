<?php

namespace App\Filament\User\Widgets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class TicketNotificationWidget extends Widget
{
    protected static string $view = 'filament.user.widgets.ticket-notification-widget';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $userId = auth()->id();
        
        // Get tickets with admin replies in last 7 days
        $ticketsWithNewReplies = Ticket::where('user_id', $userId)
            ->whereIn('status', ['open', 'in_progress'])
            ->whereHas('messages', function (Builder $query) use ($userId) {
                $query->where('is_admin_reply', true)
                    ->where('user_id', '!=', $userId)
                    ->where('created_at', '>', now()->subDays(7));
            })
            ->count();

        return [
            'ticketsWithNewReplies' => $ticketsWithNewReplies,
        ];
    }
}
