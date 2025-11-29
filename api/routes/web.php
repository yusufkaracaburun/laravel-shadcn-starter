<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\OauthController;
use App\Http\Controllers\User\LoginLinkController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('auth')->group(function (): void {
    // OAuth
    Route::get('/redirect/{provider}', [OauthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('/callback/{provider}', [OauthController::class, 'callback'])->name('oauth.callback');
    
    // Magic Link
    if (config('login-link.enabled', true)) {
        Route::middleware('throttle:login-link')->group(function (): void {
            Route::post('/login-link', [LoginLinkController::class, 'store'])->name('login-link.store');
            Route::get('/login-link/{token}', [LoginLinkController::class, 'login'])
                ->name('login-link.login')
                ->middleware('signed');
        });
    }
});
