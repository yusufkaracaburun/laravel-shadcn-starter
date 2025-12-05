<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    Auth::login($user);

    return $user;
});

Route::post('/logout', function (Request $request) {
    // For SPA authentication, we use session-based auth, not tokens
    // Only delete token if it's a PersonalAccessToken (API token auth)
    $token = $request->user()->currentAccessToken();
    if ($token && $token instanceof PersonalAccessToken) {
        $token->delete();
    }

    Auth::guard('web')->logout();

    // Only invalidate session if it exists (for SPA authentication)
    if ($request->hasSession()) {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return response()->noContent();
})->middleware('auth:sanctum');
