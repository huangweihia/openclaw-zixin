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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // OpenClaw API Token
    'openclaw' => [
        'token' => env('OPENCLAW_WEBHOOK_TOKEN', 'openclaw-ai-fetcher-2026'),
    ],

    // SVIP Subscription Token
    'svip_subscription' => [
        'token' => env('SVIP_SUBSCRIPTION_TOKEN', 'svip-subscription-2026'),
    ],

    // SBTI：无登录后台，使用强随机 token（勿泄露管理页 URL）
    'personality_quiz' => [
        'admin_token' => env('PERSONALITY_QUIZ_ADMIN_TOKEN', ''),
    ],

];
