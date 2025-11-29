<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeamController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/teams', [TeamController::class, 'index'])->name('api.teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('api.teams.store');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('api.teams.show');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('api.teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('api.teams.destroy');
    Route::put('/current-team', [TeamController::class, 'switch'])->name('api.teams.switch');
});
