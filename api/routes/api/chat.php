<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/chat/data', [ChatController::class, 'index'])->name('api.chat.data');
});
