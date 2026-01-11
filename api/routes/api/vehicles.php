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

Route::middleware('auth:sanctum')->name('api.users.')->group(function (): void {
    Route::get('/users/prerequisites', [UserController::class, 'prerequisites'])->name('prerequisites');
    Route::get('/users/all', [UserController::class, 'all'])->name('all');
    Route::get('/users/active', [UserController::class, 'active'])->name('active');
    Route::get('/users/current', [UserController::class, 'current'])->name('current');

    Route::get('/users/roles', [UserController::class, 'roles'])->name('roles');

    Route::apiResource('users', UserController::class);
});
