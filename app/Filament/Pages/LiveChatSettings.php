<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LiveChatSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static string $view = 'filament.pages.live-chat-settings';
    
    protected static ?string $navigationLabel = 'Live Chat Settings';
    
    protected static ?string $title = 'Live Chat Configuration';
    
    protected static ?int $navigationSort = 10;
    
    public function mount(): void
    {
        // Load existing settings
        $this->tawkWidgetId = setting('tawk_widget_id', '');
        $this->chatEnabled = setting('chat_enabled', true);
        $this->adminOnlineStatus = setting('admin_online_status', true);
    }
    
    public function saveSettings(): void
    {
        // Save settings to database
        setting(['tawk_widget_id' => $this->tawkWidgetId]);
        setting(['chat_enabled' => $this->chatEnabled]);
        setting(['admin_online_status' => $this->adminOnlineStatus]);
        
        $this->notify('success', 'Live chat settings saved successfully!');
    }
    
    protected function getViewData(): array
    {
        return [
            'tawkWidgetId' => $this->tawkWidgetId ?? '',
            'chatEnabled' => $this->chatEnabled ?? true,
            'adminOnlineStatus' => $this->adminOnlineStatus ?? true,
        ];
    }
}
