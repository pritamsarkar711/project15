<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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
            'channel' => env('SLACK_BOT_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IndexNow (Bing / Yandex / Seznam / Naver instant indexing)
    |--------------------------------------------------------------------------
    | Ahrefs flagged "Changed pages not submitted to IndexNow". Whenever a
    | post is created, updated, published or deleted, IndexNowService pings
    | the shared IndexNow API with the changed URL so search engines pick
    | the change up in minutes instead of weeks.
    |
    | The key must ALSO be reachable as a static file at /{key}.txt — it is
    | committed at public/78e73db1ce9890e29fd77a89f404cf5f.txt (the key is
    | public by design; it only proves ownership of the host).
    | Override with INDEXNOW_KEY in .env if it ever needs rotating.
    */
    'indexnow' => [
        'key' => env('INDEXNOW_KEY', '78e73db1ce9890e29fd77a89f404cf5f'),
        'host' => env('INDEXNOW_HOST', 'huvanti.com'),
        // Shared endpoint routes to Bing/Yandex/Seznam automatically.
        'endpoint' => 'https://api.indexnow.org/indexnow',
    ],

];
