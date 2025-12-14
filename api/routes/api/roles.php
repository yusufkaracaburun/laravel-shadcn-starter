<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;

/*
|--------------------------------------------------------------------------
| Role API Routes
|--------------------------------------------------------------------------
|
| All role-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.roles.')->group(function (): void {
    // Role resource routes
    Route::apiResource('roles', RoleController::class);
});
