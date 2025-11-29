<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeamController;

/*
|--------------------------------------------------------------------------
| Team API Routes
|--------------------------------------------------------------------------
|
| All team-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->group(function (): void {
    // Team resource routes
    Route::apiResource('teams', TeamController::class);

    // Current team switching
    Route::put('/current-team', [TeamController::class, 'switch'])->name('api.teams.switch');
});
