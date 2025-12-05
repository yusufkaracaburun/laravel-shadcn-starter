<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;

class ValidateCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // API routes are handled by Sanctum's CSRF middleware
        // This middleware is used for web routes
        'sanctum/csrf-cookie',
        'api/*',
        'login',
        'logout',
        'register',
    ];
}

