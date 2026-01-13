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

Route::middleware('auth:sanctum')->name('api.equipments.')->group(function (): void {
    Route::get('/equipments/prerequisites', [UserController::class, 'prerequisites'])->name('prerequisites');
    Route::get('/equipments/all', [UserController::class, 'all'])->name('all');
    Route::get('/equipments/active', [UserController::class, 'active'])->name('active');

    Route::apiResource('equipments', UserController::class);
});
