<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| User API Routes
|--------------------------------------------------------------------------
|
| All user-related API endpoints require authentication via Sanctum.
|
*/

Route::name('api.users.')->group(function (): void {
    // Current authenticated user
    Route::get('/user/current', [UserController::class, 'current'])->name('current');

    // User resource routes
    Route::apiResource('user', UserController::class);
})->middleware('auth:sanctum');
