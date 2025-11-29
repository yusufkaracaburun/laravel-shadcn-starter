<?php

declare(strict_types=1);

use App\Events\ExampleBroadcastEvent;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\OauthController;

// Load authentication routes
require __DIR__.'/auth.php';

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

Route::get('/reverb', function (): void {
   event(new ExampleBroadcastEvent('Hello from Reverb!'));
});