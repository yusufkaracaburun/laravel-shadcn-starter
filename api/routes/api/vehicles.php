<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VehicleController;

/*
|--------------------------------------------------------------------------
| Vehicle API Routes
|--------------------------------------------------------------------------
|
| All vehicle-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.vehicles.')->group(function (): void {
    Route::get('vehicles/prerequisites', [VehicleController::class, 'prerequisites'])->name('vehicles.prerequisites');
    Route::apiResource('vehicles', VehicleController::class);
});
