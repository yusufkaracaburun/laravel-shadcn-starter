<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EquipmentController;

Route::middleware('auth:sanctum')->name('api.equipments.')->group(function (): void {
    Route::get('equipments/prerequisites', [EquipmentController::class, 'prerequisites'])->name('prerequisites');
    Route::apiResource('equipments', EquipmentController::class);
});
