<?php

namespace App\Filament\User\Resources\TicketResource\Pages;

use App\Filament\User\Resources\TicketResource;
use App\Models\TicketMessage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'open';
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Create initial message from description
        TicketMessage::create([
            'ticket_id' => $this->record->id,
            'user_id' => auth()->id(),
            'message' => $this->record->description,
            'is_admin_reply' => false,
        ]);
    }
}
