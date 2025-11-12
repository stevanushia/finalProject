<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | These are the configuration settings for Midtrans payment gateway.
    | For sandbox testing, use sandbox keys. For production, use production keys.
    |
    */

    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    // Set to false for sandbox/testing, true for production
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Enable automatic sanitization
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    // Enable 3D Secure for credit cards
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];