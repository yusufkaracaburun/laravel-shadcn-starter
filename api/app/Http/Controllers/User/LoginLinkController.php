<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\LoginLink;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\LoginLinkMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

use function Illuminate\Support\defer;

final class LoginLinkController extends Controller
{
    private const string RATE_LIMIT_PREFIX = 'login-link:';

    /**
     * Create a new magic link.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureFeatureEnabled();

        /** @var array<string, string> $validated */
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = $validated['email'];
        $key = self::RATE_LIMIT_PREFIX . $email;
        $rateLimitAttempts = (int) config('login-link.rate_limit_attempts', 1);
        $rateLimitDecay = (int) config('login-link.rate_limit_decay', 60);

        // Rate limit check - 1 attempt per minute
        if (RateLimiter::tooManyAttempts($key, $rateLimitAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            session()->flash('error', __('Please wait :seconds seconds before requesting another magic link.', ['seconds' => $seconds]));

            return back();
        }

        $user = User::query()->where('email', $email)->firstOrFail();

        // Increment the rate limiter
        RateLimiter::hit($key, $rateLimitDecay);

        $magicLink = LoginLink::query()->create([
            'user_id'    => $user->id,
            'token'      => Str::random(64),
            'expires_at' => now()->addMinutes((int) config('login-link.expiration_minutes', 15)),
        ]);

        defer(fn () => $user->notify(new LoginLinkMail($magicLink, $user)), 'login-link-notification');

        session()->flash('success', __('Magic link sent to your email!'));

        return back();
    }

    /**
     * Login with a magic link.
     */
    public function login(string $token): RedirectResponse
    {
        $this->ensureFeatureEnabled();

        $magicLink = LoginLink::query()->where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $magicLink->update(['used_at' => now()]);
        Auth::login($magicLink->user, true);

        return redirect()->intended(config('fortify.home', '/'));
    }

    private function ensureFeatureEnabled(): void
    {
        abort_unless(config('login-link.enabled', true), 404);
    }
}
