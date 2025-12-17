<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\OauthController;
use App\Http\Controllers\Api\InvoiceController;

// Load authentication routes
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| Server-side authenticated routes for operations that cannot be handled
| by the client (e.g., OAuth provider disconnection).
|
*/

Route::middleware('auth')->group(function (): void {
    Route::delete('/auth/destroy/{provider}', [OauthController::class, 'destroy'])->name('oauth.destroy');
});

Route::get('/invoices/{invoice}/html', [InvoiceController::class, 'asHtml'])
    ->middleware('auth:sanctum');

Route::get('/invoices/{invoice}/pdf/preview', [InvoiceController::class, 'previewPdf']);

Route::get('/test/mail', function () {
    Mail::raw('Hello World', function ($message) {
        $message->to('info@emeq.nl')
            ->subject('Test Mail');
    });

    return 'Mail sent!';
});

