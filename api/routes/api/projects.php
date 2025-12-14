<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProjectController;

/*
|--------------------------------------------------------------------------
| Project API Routes
|--------------------------------------------------------------------------
|
| All project-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.projects.')->group(function (): void {
    // Project resource routes
    Route::apiResource('project', ProjectController::class);
});
