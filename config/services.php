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

    'checklist' => [
        'base_url' => env('CHECKLIST_API_BASE_URL', 'https://carvalima.unitopconsultoria.com.br/api/v2'),
        'api_prefix' => env('CHECKLIST_API_PREFIX', 'checklist'),
        'timeout' => env('CHECKLIST_API_TIMEOUT', 30),
        'retry_times' => env('CHECKLIST_API_RETRY_TIMES', 3),
        'verify_ssl' => env('CHECKLIST_API_VERIFY_SSL', false),
        'retry_sleep' => env('CHECKLIST_API_RETRY_SLEEP', 1000), // milliseconds
        'api_token' => env('CHECKLIST_API_TOKEN', 'eaZw1DbmXso3FHxx+pkHrbw+jRlcGjmeZbU9+Tb1oRA='),
        'host_header' => env('CHECKLIST_API_HOST_HEADER', 'carvalima.unitopconsultoria.com.br'),
    ],

    'smartec' => [
        'base_url' => env('SMARTEC_BASE_URL', 'https://sistema.smartec.com.br/api'),
        'token' => env('SMARTEC_TOKEN'),
        'timeout' => env('SMARTEC_TIMEOUT', 30),
    ],
];
