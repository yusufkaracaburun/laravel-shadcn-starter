<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InvoiceController;

/*
|--------------------------------------------------------------------------
| Invoice API Routes
|--------------------------------------------------------------------------
|
| All invoice-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.invoices.')->group(function (): void {
    // Invoice resource routes
    Route::apiResource('invoices', InvoiceController::class);
});
