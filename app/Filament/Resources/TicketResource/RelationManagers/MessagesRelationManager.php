<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Models\TicketMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Messages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(3)
                    ->label('Your Reply'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
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
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Reply')
                    ->using(function (array $data): TicketMessage {
                        return TicketMessage::create([
                            'ticket_id' => $this->getOwnerRecord()->id,
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc')
            ->paginated(false);
    }
}
