<?php

namespace App\Filament\Admin\Resources\ExtensionResource\Pages;

use App\Filament\Admin\Resources\ExtensionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use App\Models\Extension;

class ListExtensions extends ListRecords
{
    protected static string $resource = ExtensionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh Registry')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    $extensionManager = app(\App\Services\ExtensionManager::class);
                    $extensionManager->refreshRegistry();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Registry Refreshed')
                        ->body('Extension registry has been refreshed successfully.')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('statistics')
                ->label('View Statistics')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->modalContent(function () {
                    $stats = Extension::getStatistics();
                    
                    return view('filament.admin.pages.extension-statistics', compact('stats'));
                })
                ->modalHeading('Extension Statistics'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Extensions')
                ->badge(Extension::count()),
            'active' => Tab::make('Active')
                ->badge(Extension::active()->count())
                ->modifyQueryUsing(fn ($query) => $query->active()),
            'installed' => Tab::make('Installed')
                ->badge(Extension::installed()->where('active', false)->count())
                ->modifyQueryUsing(fn ($query) => $query->installed()->where('active', false)),
            'available' => Tab::make('Available')
                ->badge(Extension::available()->count())
                ->modifyQueryUsing(fn ($query) => $query->available()),
        ];
    }
}
