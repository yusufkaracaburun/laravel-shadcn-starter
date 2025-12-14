<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;

/*
|--------------------------------------------------------------------------
| Company API Routes
|--------------------------------------------------------------------------
|
| All company-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.companies.')->group(function (): void {
    // Company resource routes
    Route::apiResource('company', CompanyController::class);
});
