<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;

/*
|--------------------------------------------------------------------------
| Item API Routes
|--------------------------------------------------------------------------
|
| All item-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.items.')->group(function (): void {
    // Item resource routes
    Route::apiResource('items', ItemController::class);
});
