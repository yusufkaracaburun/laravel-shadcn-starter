<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| Payment API Routes
|--------------------------------------------------------------------------
|
| All payment-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.payments.')->group(function (): void {
    // Payment resource routes
    Route::apiResource('payments', PaymentController::class);
});
