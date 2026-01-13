<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeamController;

/*
|--------------------------------------------------------------------------
| Team API Routes
|--------------------------------------------------------------------------
|
| All team-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.teams.')->group(function (): void {
    Route::get('/teams/prerequisites', [TeamController::class, 'prerequisites'])->name('prerequisites');
    Route::put('/teams/current-team', [TeamController::class, 'switch'])->name('switch');

    Route::apiResource('teams', TeamController::class);
});
