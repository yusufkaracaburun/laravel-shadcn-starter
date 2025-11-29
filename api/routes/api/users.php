<?php

declare(strict_types=1);

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user/current', [UserController::class, 'current'])->name('api.user.current');
    Route::apiResource('user', UserController::class);
});

