<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use App\Models\TicketMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TicketMessagesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Messages';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TicketMessage::query()
                    ->where('ticket_id', $this->getRecord()->id)
                    ->orderBy('created_at', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('From')
                    ->badge()
                    ->color(fn (TicketMessage $record): string => $record->is_admin_reply ? 'success' : 'info'),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Time'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Reply')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->rows(3)
                            ->label('Your Reply'),
                    ])
                    ->using(function (array $data): TicketMessage {
                        return TicketMessage::create([
                            'ticket_id' => $this->getRecord()->id,
                            'user_id' => auth()->id(),
                            'message' => $data['message'],
                            'is_admin_reply' => true,
                        ]);
                    })
                    ->after(function () {
                        Notification::make()
                            ->title('Reply sent successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->paginated(false);
    }
}
