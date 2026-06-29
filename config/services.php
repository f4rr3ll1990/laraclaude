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

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
    ],

    'puter' => [
        // Auth token from `node ptr_img_gen/auth.js` (one-time browser confirm).
        'token' => env('PUTER_AUTH_TOKEN'),
        'model' => env('PUTER_IMAGE_MODEL', 'gpt-image-2'),
        'quality' => env('PUTER_IMAGE_QUALITY', 'low'),
        // Node binary and the generator script that bridges to puter.js.
        'node' => env('NODE_BINARY', 'node'),
        'script_dir' => env('PUTER_SCRIPT_DIR', base_path('ptr_img_gen')),
    ],

];
