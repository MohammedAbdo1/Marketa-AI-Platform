<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/api/auth/google/callback'),
    ],

    'python_ai' => [
        // Default to localhost for development, Docker 'api' hostname for production
        'url' => env('PYTHON_AI_URL', env('APP_ENV') === 'production' ? 'http://api:8001/api' : 'http://localhost:8001/api'),
        'timeout' => env('PYTHON_AI_TIMEOUT', 180),  // 180 seconds to allow Google API calls
        'retry_attempts' => env('PYTHON_AI_RETRY_ATTEMPTS', 3),
        'websocket_url' => env('PYTHON_AI_WEBSOCKET_URL', 'http://localhost:8001'),
        'health_check_url' => env('PYTHON_AI_HEALTH_URL', 'http://localhost:8001/health'),
        // Fast path toggle and simple base URL (no queues/polling)
        'use_simple_ai' => env('USE_SIMPLE_AI', false),
        'simple_url' => env('PYTHON_AI_SIMPLE_URL', env('PYTHON_AI_URL', 'http://localhost:8001/api')),
    ],

];
