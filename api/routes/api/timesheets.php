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

Route::middleware('auth:sanctum')->name('api.timesheets.')->group(function (): void {
    Route::get('/timesheets/prerequisites', [UserController::class, 'prerequisites'])->name('prerequisites');
    Route::get('/timesheets/all', [UserController::class, 'all'])->name('all');
    Route::get('/timesheets/{status}', [UserController::class, 'active'])->name('active');

    Route::apiResource('timesheets', UserController::class);
});
