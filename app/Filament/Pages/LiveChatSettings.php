<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class LiveChatSettings extends Page
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static string $view = 'filament.pages.live-chat-settings';
    
    protected static ?string $navigationLabel = 'Live Chat Settings';
    
    protected static ?string $title = 'Live Chat Configuration';
    
    protected static ?int $navigationSort = 10;
    
    public $tawkWidgetId = '';
    public $chatEnabled = true;
    public $adminOnlineStatus = true;
    
    public function mount(): void
    {
        // Load existing settings from database or use defaults
        $this->tawkWidgetId = $this->getSetting('tawk_widget_id', '');
        $this->chatEnabled = $this->getSetting('chat_enabled', true);
        $this->adminOnlineStatus = $this->getSetting('admin_online_status', true);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('tawkWidgetId')
                    ->label('Tawk.to Widget ID')
                    ->placeholder('Enter your Tawk.to Widget ID')
                    ->helperText('Get your Widget ID from tawk.to dashboard'),
                    
                Toggle::make('chatEnabled')
                    ->label('Enable Live Chat')
                    ->default(true),
                    
                Toggle::make('adminOnlineStatus')
                    ->label('Show Admin Online Status')
                    ->default(true),
            ])
            ->statePath('data');
    }
    
    public function saveSettings(): void
    {
        $this->validate([
            'tawkWidgetId' => 'nullable|string',
            'chatEnabled' => 'boolean',
            'adminOnlineStatus' => 'boolean',
        ]);
        
        // Save settings to database
        $this->setSetting('tawk_widget_id', $this->tawkWidgetId);
        $this->setSetting('chat_enabled', $this->chatEnabled);
        $this->setSetting('admin_online_status', $this->adminOnlineStatus);
        
        Notification::make()
            ->title('Live chat settings saved successfully!')
            ->success()
            ->send();
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->action('saveSettings')
                ->color('primary'),
        ];
    }
    
    private function getSetting($key, $default = null)
    {
        // Use Laravel's config or database settings
        return config("livechat.{$key}", $default);
    }
    
    private function setSetting($key, $value)
    {
        // Save to config or database
        config(["livechat.{$key}" => $value]);
    }
    
    protected function getViewData(): array
    {
        return [
            'tawkWidgetId' => $this->tawkWidgetId,
            'chatEnabled' => $this->chatEnabled,
            'adminOnlineStatus' => $this->adminOnlineStatus,
        ];
    }
}
