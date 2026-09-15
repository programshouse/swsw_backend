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


    'kashier' => [
        'base_url' => env(
            'KASHIER_BASE_URL',
            'https://api.kashier.io'
        ),

        'merchant_id' => env('KASHIER_MERCHANT_ID'),
        'secret_key' => env('KASHIER_SECRET_KEY'),
        'api_key' => env('KASHIER_API_KEY'),

        'currency' => env(
            'KASHIER_CURRENCY',
            'EGP'
        ),

        'timeout' => env(
            'KASHIER_TIMEOUT',
            30
        ),

        'merchant_redirect' => env(
            'KASHIER_MERCHANT_REDIRECT'
        ),

        'webhook_url' => env(
            'KASHIER_WEBHOOK_URL'
        ),

        'webhook_secret' => env(
            'KASHIER_WEBHOOK_SECRET'
        ),
    ],


    'kashier_payout' => [
        'api_base_url' =>
        'https://api.kashier.io',

        'transfer_base_url' =>
        'https://fep.kashier.io/v3',

        'secret_key' =>
        '4e0c37e0cd45780f6d8b44e2b2ce7bfe$567f3648a92208196605d8429305942cca58fe540dfbd82f6e502e68eaacae51d32a97ce7625bafee61fa6a0ad882025',

        'timeout' =>
        30,
    ],


    // 4e0c37e0cd45780f6d8b44e2b2ce7bfe$567f3648a92208196605d8429305942cca58fe540dfbd82f6e502e68eaacae51d32a97ce7625bafee61fa6a0ad882025

    //https://api.kashier.io   api_base_url

    //https://fep.kashier.io/v3/transfers/single

    //  'kashier_payout' => [
    //     'api_base_url' => env(
    //         'KASHIER_PAYOUT_API_BASE_URL',
    //         'https://test-api.kashier.io/v2'
    //     ),

    //     'transfer_base_url' => env(
    //         'KASHIER_PAYOUT_TRANSFER_BASE_URL',
    //         'https://test-fep.kashier.io/v3'
    //     ),

    //     'secret_key' => env(
    //         '$4c52e9baf61ce1bd01fccd73f4d25052f3e5b403bc8c0c701d05a46cc39fa2e5436b087f44034248f8b5864a1756bf08'
    //     ),

    //     'timeout' => (int) env(
    //         'KASHIER_PAYOUT_TIMEOUT',
    //         30
    //     ),
    // ],

];
