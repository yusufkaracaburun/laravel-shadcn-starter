<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InvoiceController;

/*
|--------------------------------------------------------------------------
| Invoice API Routes
|--------------------------------------------------------------------------
|
| All invoice-related API endpoints require authentication via Sanctum.
|
*/

Route::middleware('auth:sanctum')->name('api.invoices.')->group(function (): void {
    Route::get('invoices/prerequisites', [InvoiceController::class, 'prerequisites'])->name('prerequisites');

    // Download invoice PDF
    Route::get('/invoices/{invoice}/pdf/preview', [InvoiceController::class, 'previewPdf'])->name('invoices.preview-pdf');
    Route::post('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('download-pdf');

    // Invoice resource routes
    Route::apiResource('invoices', InvoiceController::class);
});
