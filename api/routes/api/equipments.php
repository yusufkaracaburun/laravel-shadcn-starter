<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EquipmentController;

Route::prefix('equipments')->name('equipments.')->group(function () {
    Route::get('prerequisites', [EquipmentController::class, 'prerequisites'])->name('prerequisites');
    Route::apiResource('equipments', EquipmentController::class);
});
