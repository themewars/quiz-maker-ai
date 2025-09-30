<?php

namespace App\Filament\User\Resources\QuizzesResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $recordTitleAttribute = 'title';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->width('70px'),
                TextColumn::make('title')->label(__('messages.common.question'))->wrap(),
                TextColumn::make('answers_count')->counts('answers')->label(__('messages.common.answer'))->badge(),
            ])
            ->defaultSort('id', 'asc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading(__('messages.quiz.no_questions_found') ?? 'No questions found')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}


