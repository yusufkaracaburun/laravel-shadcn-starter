<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Toggle
    |--------------------------------------------------------------------------
    |
    | Quickly enable / disable the login link flow. When disabled the routes
    | remain registered but the controller returns a 404.
    |
    */
    'enabled' => env('LOGIN_LINK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure how many attempts a user can make and how long (in seconds)
    | they must wait before requesting another link.
    |
    */
    'rate_limit_attempts' => (int) env('LOGIN_LINK_RATE_LIMIT_ATTEMPTS', 1),
    'rate_limit_decay' => (int) env('LOGIN_LINK_RATE_LIMIT_DECAY', 60),

    /*
    |--------------------------------------------------------------------------
    | Link Expiration (minutes)
    |--------------------------------------------------------------------------
    */
    'expiration_minutes' => (int) env('LOGIN_LINK_EXPIRATION', 15),
];
