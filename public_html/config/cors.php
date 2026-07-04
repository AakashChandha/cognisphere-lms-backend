<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Browsers block cross-origin requests unless the API allows the frontend
    | origin. Set CORS_ALLOWED_ORIGINS in .env (comma-separated URLs).
    |
    | Use * to allow all origins (token auth, no cookies):
    |   CORS_ALLOWED_ORIGINS=*
    |
    | Or list explicit frontend URLs (recommended for production):
    |   CORS_ALLOWED_ORIGINS=http://localhost:5173,https://app.example.com
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'openapi.yaml'],

    'allowed_methods' => ['*'],

    'allowed_origins' => env('CORS_ALLOWED_ORIGINS') === '*'
        ? ['*']
        : array_values(array_filter(array_map('trim', explode(',', env(
            'CORS_ALLOWED_ORIGINS',
            'http://localhost:5173,http://localhost:3000,http://127.0.0.1:5173,http://127.0.0.1:3000'
        ))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
