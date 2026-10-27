<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'gemini'),
        'gemini_key' => env('GEMINI_API_KEY', ''),
        'openai_key' => env('OPENAI_API_KEY', ''),

        // Danh sách model Gemini thử lần lượt (có thể ghi đè bằng GEMINI_MODELS="a,b,c")
        'gemini_models' => array_values(array_filter(array_map('trim', explode(',', env(
            'GEMINI_MODELS',
            'gemini-3.5-flash-lite,gemini-flash-lite-latest,gemini-3.7-flash,gemini-3.1-flash-lite,gemini-3.8-flash'
        ))))),
        'gemini_endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models'),

        'openai_model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'openai_endpoint' => env('OPENAI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),

        'timeout' => (int) env('AI_HTTP_TIMEOUT', 10),
    ],

];
