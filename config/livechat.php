<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Live Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the live chat system using Tawk.to
    |
    */

    'tawk_widget_id' => env('TAWK_WIDGET_ID', ''),
    'chat_enabled' => env('CHAT_ENABLED', true),
    'admin_online_status' => env('ADMIN_ONLINE_STATUS', true),
];
