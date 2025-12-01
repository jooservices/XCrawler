<?php

return [
    'name' => 'Flick',

    // FlickrHub API URL
    'hub_url' => env('FLICKR_HUB_URL', 'http://localhost:8000'),

    // Callback URL for FlickrHub to send results
    // This should be accessible from FlickrHub (use host.docker.internal for Docker)
    'callback_url' => env('FLICKR_CALLBACK_URL', 'http://host.docker.internal/api/flick/callback'),

    // Maximum recursion depth
    'max_depth' => env('FLICK_MAX_DEPTH', 3),

    // Telegram Bot Configuration
    'telegram_bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'telegram_chat_id' => env('TELEGRAM_CHAT_ID'),
];
