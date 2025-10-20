<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExtensionResource\Pages;
use App\Models\Extension;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Services\ExtensionManager;
use Illuminate\Database\Eloquent\Collection;

class ExtensionResource extends Resource
{
    protected static ?string $model = Extension::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Extensions';

    protected static ?string $modelLabel = 'Extension';

    protected static ?string $pluralModelLabel = 'Extensions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Extension Information')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Extension ID')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),
                        Forms\Components\TextInput::make('name')
                            ->label('Extension Name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),
                        Forms\Components\TextInput::make('version')
                            ->label('Version')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->disabled(),
                        Forms\Components\TextInput::make('author')
                            ->label('Author')
                            ->maxLength(255)
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('installed')
                            ->label('Installed')
                            ->disabled(),
                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('installed_at')
                            ->label('Installed At')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('activated_at')
                            ->label('Activated At')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\KeyValue::make('config')
                            ->label('Configuration')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Config')
                            ->disabled(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Extension Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('author')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'installed' => 'warning',
                        'available' => 'secondary',
                        default => 'secondary',
                    }),
                Tables\Columns\IconColumn::make('installed')
                    ->label('Installed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('installed_at')
                    ->label('Installed At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'installed' => 'Installed',
                        'available' => 'Available',
                    ]),
                TernaryFilter::make('installed')
                    ->label('Installed')
                    ->boolean()
                    ->trueLabel('Installed only')
                    ->falseLabel('Not installed only')
                    ->native(false),
                TernaryFilter::make('active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Not active only')
                    ->native(false),
                SelectFilter::make('author')
                    ->label('Author')
                    ->options(function () {
                        return Extension::distinct('author')
                            ->pluck('author', 'author')
                            ->filter()
                            ->toArray();
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->infolist([
                        Infolists\Components\Section::make('Extension Details')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Extension Name'),
                                Infolists\Components\TextEntry::make('version')
                                    ->label('Version'),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Description'),
                                Infolists\Components\TextEntry::make('author')
                                    ->label('Author'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'installed' => 'warning',
                                        'available' => 'secondary',
                                        default => 'secondary',
                                    }),
                            ])
                            ->columns(2),
                        Infolists\Components\Section::make('Dependencies')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('dependencies')
                                    ->label('Dependencies')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('dependency')
                                            ->label('Dependency'),
                                    ]),
                            ])
                            ->collapsible(),
                        Infolists\Components\Section::make('Requirements')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('requirements')
                                    ->label('Requirements')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('type')
                                            ->label('Type'),
                                        Infolists\Components\TextEntry::make('version')
                                            ->label('Version'),
                                    ]),
                            ])
                            ->collapsible(),
                    ]),
                Action::make('install')
                    ->label('Install')
                    ->icon('heroicon-o-download')
                    ->color('success')
                    ->visible(fn (Extension $record): bool => !$record->installed)
                    ->requiresConfirmation()
                    ->modalHeading('Install Extension')
                    ->modalDescription('Are you sure you want to install this extension?')
                    ->action(function (Extension $record) {
                        $extensionManager = app(ExtensionManager::class);
                        $result = $extensionManager->installExtension($record->id);
                        
                        if ($result) {
                            \Filament\Notifications\Notification::make()
                                ->title('Extension Installed')
                                ->body("Extension '{$record->name}' has been installed successfully.")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Installation Failed')
                                ->body("Failed to install extension '{$record->name}'.")
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('uninstall')
                    ->label('Uninstall')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (Extension $record): bool => $record->installed)
                    ->requiresConfirmation()
                    ->modalHeading('Uninstall Extension')
                    ->modalDescription('Are you sure you want to uninstall this extension? This action cannot be undone.')
                    ->action(function (Extension $record) {
                        $extensionManager = app(ExtensionManager::class);
                        $result = $extensionManager->uninstallExtension($record->id);
                        
                        if ($result) {
                            \Filament\Notifications\Notification::make()
                                ->title('Extension Uninstalled')
                                ->body("Extension '{$record->name}' has been uninstalled successfully.")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Uninstallation Failed')
                                ->body("Failed to uninstall extension '{$record->name}'.")
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Extension $record): bool => $record->installed && !$record->active)
                    ->requiresConfirmation()
                    ->modalHeading('Activate Extension')
                    ->modalDescription('Are you sure you want to activate this extension?')
                    ->action(function (Extension $record) {
                        $extensionManager = app(ExtensionManager::class);
                        $result = $extensionManager->activateExtension($record->id);
                        
                        if ($result) {
                            \Filament\Notifications\Notification::make()
                                ->title('Extension Activated')
                                ->body("Extension '{$record->name}' has been activated successfully.")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Activation Failed')
                                ->body("Failed to activate extension '{$record->name}'.")
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Extension $record): bool => $record->active)
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Extension')
                    ->modalDescription('Are you sure you want to deactivate this extension?')
                    ->action(function (Extension $record) {
                        $extensionManager = app(ExtensionManager::class);
                        $result = $extensionManager->deactivateExtension($record->id);
                        
                        if ($result) {
                            \Filament\Notifications\Notification::make()
                                ->title('Extension Deactivated')
                                ->body("Extension '{$record->name}' has been deactivated successfully.")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Deactivation Failed')
                                ->body("Failed to deactivate extension '{$record->name}'.")
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('install_selected')
                        ->label('Install Selected')
                        ->icon('heroicon-o-download')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Install Selected Extensions')
                        ->modalDescription('Are you sure you want to install the selected extensions?')
                        ->action(function (Collection $records) {
                            $extensionManager = app(ExtensionManager::class);
                            $successCount = 0;
                            
                            foreach ($records as $record) {
                                if (!$record->installed) {
                                    if ($extensionManager->installExtension($record->id)) {
                                        $successCount++;
                                    }
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Bulk Installation Complete')
                                ->body("Successfully installed {$successCount} extensions.")
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('activate_selected')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Activate Selected Extensions')
                        ->modalDescription('Are you sure you want to activate the selected extensions?')
                        ->action(function (Collection $records) {
                            $extensionManager = app(ExtensionManager::class);
                            $successCount = 0;
                            
                            foreach ($records as $record) {
                                if ($record->installed && !$record->active) {
                                    if ($extensionManager->activateExtension($record->id)) {
                                        $successCount++;
                                    }
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Bulk Activation Complete')
                                ->body("Successfully activated {$successCount} extensions.")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExtensions::route('/'),
            'create' => Pages\CreateExtension::route('/create'),
            'view' => Pages\ViewExtension::route('/{record}'),
            'edit' => Pages\EditExtension::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
