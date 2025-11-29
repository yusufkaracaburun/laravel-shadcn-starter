<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\OauthController;
use App\Http\Controllers\User\LoginLinkController;

// OAuth routes (server-side callbacks)
Route::prefix('auth')->group(function (): void {
    Route::get('/redirect/{provider}', [OauthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('/callback/{provider}', [OauthController::class, 'callback'])->name('oauth.callback');

    // Login link routes (server-side)
    if (config('login-link.enabled', true)) {
        Route::middleware('throttle:login-link')->group(function (): void {
            Route::post('/login-link', [LoginLinkController::class, 'store'])->name('login-link.store');
            Route::get('/login-link/{token}', [LoginLinkController::class, 'login'])
                ->name('login-link.login')
                ->middleware('signed');
        });
    }
});

// Authenticated routes for server-side operations only
Route::middleware('auth')->group(function (): void {
    // OAuth disconnect
    Route::delete('/auth/destroy/{provider}', [OauthController::class, 'destroy'])->name('oauth.destroy');
});
