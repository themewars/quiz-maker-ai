<?php

namespace App\Filament\User\Resources\TicketResource\Pages;

use App\Filament\User\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Check if ticket is resolved or closed
        if (in_array($this->record->status, ['resolved', 'closed'])) {
            Notification::make()
                ->title('Cannot edit resolved/closed tickets')
                ->body('This ticket has been resolved or closed. Please create a new ticket if you need further assistance.')
                ->warning()
                ->send();
                
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => !in_array($this->record->status, ['resolved', 'closed'])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
