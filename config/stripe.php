<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    |
    | These are your Stripe API keys. You can find them in your Stripe
    | Dashboard under Developers -> API keys.
    |
    */

    'secret' => env('STRIPE_SECRET'),
    'key' => env('STRIPE_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe API Version
    |--------------------------------------------------------------------------
    |
    | This is the Stripe API version to use.
    |
    */

    'version' => '2023-10-16',
];
