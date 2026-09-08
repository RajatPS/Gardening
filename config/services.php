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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'base_uri' => env('OPENAI_API_BASE_URI', 'https://api.openai.com/v1'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env(
            'GOOGLE_REDIRECT_URI',
            rtrim(env('APP_URL', 'http://localhost:8000'), '/') . '/auth/google/callback'
        ),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'base_uri' => env('DEEPSEEK_API_BASE_URI', 'https://api.deepseek.ai/v1'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME', env('CLOUDINARY_NAME', env('cloudinary_NAME'))),
        'api_key' => env('CLOUDINARY_API_KEY', env('cloudinary_API_KEY')),
        'api_secret' => env('CLOUDINARY_API_SECRET', env('cloudinary_API_SECRET')),
        'url' => env('CLOUDINARY_URL'),
    ],

    'razorpay' => [
        'gateway' => env('PAYMENT_GATEWAY', 'razorpay'),
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
    ],

    'geoapify' => [
        'key' => env('GEOAPIFY_API_KEY'),
    ],

];
