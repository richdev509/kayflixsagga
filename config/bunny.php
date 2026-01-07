<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bunny.net Stream Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Bunny.net Stream API integration
    |
    */

    'api_key' => env('BUNNY_API_KEY'),

    'stream' => [
        'library_id' => env('BUNNY_STREAM_LIBRARY_ID'),
        'cdn_hostname' => env('BUNNY_STREAM_CDN_HOSTNAME'),
        'token_key' => env('BUNNY_STREAM_TOKEN_KEY'),
    ],

    'api_url' => 'https://video.bunnycdn.com',

    'stream_url' => 'https://iframe.mediadelivery.net',
];
