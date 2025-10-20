<?php

namespace App\Extensions\LiveChat;

use App\Extensions\Extension;

class LiveChatExtension extends Extension
{
    protected function initializeExtension(): void
    {
        $this->id = 'live_chat';
        $this->name = 'Live Chat (Tawk.to)';
        $this->description = 'Add live chat functionality to your website using Tawk.to integration';
        $this->version = '1.0.0';
        $this->author = 'ExamGenerator Team';
        
        $this->dependencies = [];
        $this->requirements = [
            [
                'type' => 'php_version',
                'version' => '8.0'
            ],
            [
                'type' => 'laravel_version',
                'version' => '10.0'
            ]
        ];
        
        $this->permissions = [
            'live_chat.view',
            'live_chat.manage',
            'live_chat.settings'
        ];
        
        $this->menuItems = [
            [
                'label' => 'Live Chat',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'url' => '/admin/live-chat',
                'group' => 'Communication',
                'sort' => 1
            ]
        ];
        
        $this->routes = [
            [
                'method' => 'get',
                'uri' => 'admin/live-chat',
                'action' => 'App\\Http\\Controllers\\LiveChatController@index',
                'middleware' => ['auth', 'role:admin']
            ],
            [
                'method' => 'post',
                'uri' => 'admin/live-chat/settings',
                'action' => 'App\\Http\\Controllers\\LiveChatController@updateSettings',
                'middleware' => ['auth', 'role:admin']
            ]
        ];
        
        $this->migrations = [
            database_path('migrations/extensions/live_chat/create_live_chat_settings_table.php')
        ];
        
        $this->assets = [
            'css' => [
                'extensions/live-chat/live-chat.css'
            ],
            'js' => [
                'extensions/live-chat/live-chat.js'
            ]
        ];
        
        $this->translations = [
            'en' => [
                'live_chat.title' => 'Live Chat',
                'live_chat.description' => 'Manage live chat settings',
                'live_chat.enabled' => 'Enable Live Chat',
                'live_chat.widget_id' => 'Widget ID',
                'live_chat.settings' => 'Chat Settings'
            ]
        ];
        
        $this->settingsSchema = [
            [
                'name' => 'enabled',
                'type' => 'boolean',
                'label' => 'Enable Live Chat',
                'default' => false,
                'required' => false
            ],
            [
                'name' => 'widget_id',
                'type' => 'text',
                'label' => 'Tawk.to Widget ID',
                'default' => '',
                'required' => true,
                'validation' => 'required_if:enabled,true'
            ],
            [
                'name' => 'position',
                'type' => 'select',
                'label' => 'Widget Position',
                'options' => [
                    'bottom-right' => 'Bottom Right',
                    'bottom-left' => 'Bottom Left',
                    'top-right' => 'Top Right',
                    'top-left' => 'Top Left'
                ],
                'default' => 'bottom-right',
                'required' => false
            ],
            [
                'name' => 'theme',
                'type' => 'select',
                'label' => 'Widget Theme',
                'options' => [
                    'light' => 'Light',
                    'dark' => 'Dark',
                    'auto' => 'Auto'
                ],
                'default' => 'light',
                'required' => false
            ]
        ];
        
        $this->hooks = [
            'app.before_render' => [$this, 'injectChatWidget'],
            'admin.dashboard.widgets' => [$this, 'addDashboardWidget']
        ];
        
        $this->config = [
            'enabled' => false,
            'widget_id' => '',
            'position' => 'bottom-right',
            'theme' => 'light'
        ];
        
        // Load status from database
        $this->loadExtensionStatus();
    }

    /**
     * Inject chat widget into the page
     */
    public function injectChatWidget($event): void
    {
        if (!$this->active || !$this->config['enabled'] || empty($this->config['widget_id'])) {
            return;
        }

        $widgetId = $this->config['widget_id'];
        $position = $this->config['position'];
        $theme = $this->config['theme'];

        $script = "
        <!-- Tawk.to Script -->
        <script type=\"text/javascript\">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function(){
        var s1=document.createElement(\"script\"),s0=document.getElementsByTagName(\"script\")[0];
        s1.async=true;
        s1.src='https://embed.tawk.to/{$widgetId}/default';
        s1.charset='UTF-8';
        s1.setAttribute('crossorigin','*');
        s0.parentNode.insertBefore(s1,s0);
        })();
        </script>
        <!-- End Tawk.to Script -->
        ";

        // Inject script into page
        echo $script;
    }

    /**
     * Add dashboard widget for live chat
     */
    public function addDashboardWidget($event): void
    {
        if (!$this->active) {
            return;
        }

        $widget = [
            'title' => 'Live Chat Status',
            'content' => $this->config['enabled'] ? 'Live chat is active' : 'Live chat is disabled',
            'icon' => 'heroicon-o-chat-bubble-left-right',
            'color' => $this->config['enabled'] ? 'success' : 'warning',
            'url' => '/admin/live-chat'
        ];

        $event->widgets[] = $widget;
    }

    /**
     * Get extension documentation
     */
    public function getDocumentation(): string
    {
        return "
        # Live Chat Extension Documentation

        ## Overview
        The Live Chat extension integrates Tawk.to live chat functionality into your website.

        ## Installation
        1. Install the extension from the admin panel
        2. Activate the extension
        3. Configure your Tawk.to widget ID in settings

        ## Configuration
        - **Widget ID**: Your Tawk.to widget ID
        - **Position**: Where to display the chat widget
        - **Theme**: Light, dark, or auto theme

        ## Usage
        Once configured, the chat widget will appear on your website according to your settings.

        ## Support
        For support, contact the ExamGenerator team.
        ";
    }

    /**
     * Get extension changelog
     */
    public function getChangelog(): array
    {
        return [
            '1.0.0' => [
                'Initial release',
                'Tawk.to integration',
                'Admin panel configuration',
                'Dashboard widget'
            ]
        ];
    }

    /**
     * Get extension support information
     */
    public function getSupportInfo(): array
    {
        return [
            'author' => $this->author,
            'version' => $this->version,
            'documentation' => $this->getDocumentation(),
            'support_email' => 'support@examgenerator.ai',
            'support_url' => 'https://examgenerator.ai/support'
        ];
    }
}
