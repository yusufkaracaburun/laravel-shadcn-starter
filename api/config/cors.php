<?php

declare(strict_types=1);

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'logout', 'login', 'register'],

    'allowed_methods' => ['*'],

    'allowed_origins' => env('CORS_ALLOWED_ORIGINS')
        ? explode(',', (string) env('CORS_ALLOWED_ORIGINS'))
        : ['http://localhost:5173', 'http://localhost:3000', 'http://127.0.0.1:5173', 'http://127.0.0.1:3000', 'http://127.0.0.1:8000', 'http://localhost:8000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
