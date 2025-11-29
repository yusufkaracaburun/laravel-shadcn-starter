<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/chat/data', [ChatController::class, 'index'])->name('api.chat.data');
});

