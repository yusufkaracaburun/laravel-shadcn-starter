<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\OauthController;
use App\Http\Controllers\User\LoginLinkController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle authentication flows that require server-side
| processing (OAuth callbacks, magic links, etc.). All other routes
| are handled by the Vue client via Vue Router.
|
*/

// Sanctum route for SPA authentication (CSRF cookie)
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);


Route::prefix('auth')->group(function (): void {
    // OAuth Provider Routes
    Route::get('/redirect/{provider}', [OauthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('/callback/{provider}', [OauthController::class, 'callback'])->name('oauth.callback');

    // Magic Link Routes (if enabled)
    if (config('login-link.enabled', true)) {
        Route::middleware('throttle:login-link')->group(function (): void {
            Route::post('/login-link', [LoginLinkController::class, 'store'])->name('login-link.store');
            Route::get('/login-link/{token}', [LoginLinkController::class, 'login'])
                ->name('login-link.login')
                ->middleware('signed');
        });
    }
});
