<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PermissionController;

/*
|--------------------------------------------------------------------------
| Permission API Routes
|--------------------------------------------------------------------------
|
| All permission-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.permissions.')->group(function (): void {
    // Permission resource routes
    Route::apiResource('permissions', PermissionController::class);
});
