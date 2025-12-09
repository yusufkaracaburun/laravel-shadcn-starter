<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;

/*
|--------------------------------------------------------------------------
| Customer API Routes
|--------------------------------------------------------------------------
|
| All customer-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.customers.')->group(function (): void {
    // Customer resource routes
    Route::apiResource('customers', CustomerController::class);
});
