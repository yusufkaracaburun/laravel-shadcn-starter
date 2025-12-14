<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// API Health Check (remove in production if not needed)
if (config('app.debug')) {
    Route::get('/test', fn () => response()->json(['message' => 'API is working!']));
}

// API Resource Routes
require __DIR__.'/api/users.php';
require __DIR__.'/api/teams.php';
require __DIR__.'/api/companies.php';
require __DIR__.'/api/invoices.php';
require __DIR__.'/api/items.php';
require __DIR__.'/api/payments.php';
require __DIR__.'/api/customers.php';
require __DIR__.'/api/permissions.php';
require __DIR__.'/api/roles.php';
