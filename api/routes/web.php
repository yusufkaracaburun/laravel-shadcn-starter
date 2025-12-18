<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Mail\InvoiceMail;
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
        $recipientEmail = config('app.admin_email');

        $message->to($recipientEmail)
            ->subject('Test Mail');
    });

    return 'Mail sent!';
});

Route::get('/test/mail/invoice/{id}', function (int $id) {
    $invoice = Invoice::query()->findOrFail($id);
    $recipientEmail = config('app.admin_email');

    Mail::to($recipientEmail)->send(new InvoiceMail($invoice));

    return 'Invoice email sent successfully!';
});
