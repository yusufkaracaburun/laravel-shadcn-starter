<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user/current', [UserController::class, 'current'])->name('api.user.current');
    Route::apiResource('user', UserController::class);
});
