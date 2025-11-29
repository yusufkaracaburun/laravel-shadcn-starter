<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\LoginLink;
use Illuminate\Support\Facades\URL;
use App\Notifications\LoginLinkMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    // Increase rate limit for tests to avoid throttling issues
    Config::set('login-link.rate_limit_attempts', 100);
    Config::set('login-link.rate_limit_decay', 1);

    // Clear all possible rate limiter keys
    // The throttle middleware uses email or IP as the key: login-link:{email|ip}
    $cache = app('cache')->store();
    $prefix = config('cache.prefix', '');

    // Clear common keys
    RateLimiter::clear('login-link:127.0.0.1');
    RateLimiter::clear('login-link:');
    RateLimiter::clear('login-link:test@example.com');
    RateLimiter::clear('login-link:invalid-email');
    RateLimiter::clear('login-link:nonexistent@example.com');
});

test('login link store creates magic link for valid email', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    Notification::fake();
    $email = 'test-'.uniqid().'@example.com';
    $user = User::factory()->create(['email' => $email]);
    RateLimiter::clear('login-link:'.$email);

    // Act
    $response = $this->from('/auth/login')->post('/auth/login-link', [
        'email' => $email,
    ]);

    // Assert
    $response->assertRedirect('/auth/login');
    expect(LoginLink::where('user_id', $user->id)->count())->toBe(1);
    Notification::assertSentTo($user, LoginLinkMail::class);
});

test('login link store validates email is required', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    // Clear rate limiter for IP (throttle uses IP when email is not provided)
    RateLimiter::clear('login-link:127.0.0.1');

    // Act
    $response = $this->from('/auth/login')->post('/auth/login-link', []);

    // Assert
    $response->assertSessionHasErrors(['email']);
    $response->assertRedirect('/auth/login');
});

test('login link store validates email format', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    // Clear rate limiter for invalid email (throttle uses email when provided)
    RateLimiter::clear('login-link:invalid-email');

    // Act
    $response = $this->from('/auth/login')->post('/auth/login-link', [
        'email' => 'invalid-email',
    ]);

    // Assert
    $response->assertSessionHasErrors(['email']);
    $response->assertRedirect('/auth/login');
});

test('login link store validates email exists', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    // Clear rate limiter for this email
    RateLimiter::clear('login-link:nonexistent@example.com');

    // Act
    $response = $this->from('/auth/login')->post('/auth/login-link', [
        'email' => 'nonexistent@example.com',
    ]);

    // Assert
    $response->assertSessionHasErrors(['email']);
    $response->assertRedirect('/auth/login');
});

test('login link store respects rate limiting', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    Config::set('login-link.rate_limit_attempts', 1);
    Config::set('login-link.rate_limit_decay', 60);
    $email = 'ratelimit-'.uniqid().'@example.com';
    $user = User::factory()->create(['email' => $email]);
    $key = 'login-link:'.$email;
    // Hit the rate limiter to simulate a previous request
    RateLimiter::hit($key, 60);

    // Act
    $response = $this->from('/auth/login')->post('/auth/login-link', [
        'email' => $email,
    ]);

    // Assert
    $response->assertRedirect('/auth/login');
    $response->assertSessionHas('error');
});

test('login link login authenticates user with valid token', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    // Clear rate limiter for IP (throttle middleware uses IP for GET requests)
    RateLimiter::clear('login-link:127.0.0.1');
    $user = User::factory()->create();
    $loginLink = LoginLink::create([
        'user_id' => $user->id,
        'token' => 'valid-token-123',
        'expires_at' => now()->addMinutes(15),
    ]);
    $signedUrl = URL::signedRoute('login-link.login', ['token' => 'valid-token-123']);

    // Act
    $response = $this->get($signedUrl);

    // Assert
    $response->assertRedirect();
    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
    expect($loginLink->fresh()->used_at)->not->toBeNull();
});

test('login link login fails with expired token', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    // Clear rate limiter for IP (throttle middleware uses IP for GET requests)
    RateLimiter::clear('login-link:127.0.0.1');
    $user = User::factory()->create();
    LoginLink::create([
        'user_id' => $user->id,
        'token' => 'expired-token',
        'expires_at' => now()->subMinute(),
    ]);
    $signedUrl = URL::signedRoute('login-link.login', ['token' => 'expired-token']);

    // Act
    $response = $this->get($signedUrl);

    // Assert
    $response->assertNotFound();
    expect(Auth::check())->toBeFalse();
});

test('login link login fails with used token', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    // Clear rate limiter for IP (throttle middleware uses IP for GET requests)
    RateLimiter::clear('login-link:127.0.0.1');
    $user = User::factory()->create();
    LoginLink::create([
        'user_id' => $user->id,
        'token' => 'used-token',
        'expires_at' => now()->addMinutes(15),
        'used_at' => now()->subMinute(),
    ]);
    $signedUrl = URL::signedRoute('login-link.login', ['token' => 'used-token']);

    // Act
    $response = $this->get($signedUrl);

    // Assert
    $response->assertNotFound();
    expect(Auth::check())->toBeFalse();
});

test('login link login fails with invalid token', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    // Clear rate limiter for IP (throttle middleware uses IP for GET requests)
    RateLimiter::clear('login-link:127.0.0.1');
    $signedUrl = URL::signedRoute('login-link.login', ['token' => 'invalid-token-123']);

    // Act
    $response = $this->get($signedUrl);

    // Assert
    $response->assertNotFound();
    expect(Auth::check())->toBeFalse();
});

test('login link routes return 404 when feature is disabled', function () {
    // Arrange
    Config::set('login-link.enabled', false);
    // Clear rate limiter for IP (throttle middleware uses IP for GET requests)
    RateLimiter::clear('login-link:127.0.0.1');
    $user = User::factory()->create();

    // Act
    $storeResponse = $this->from('/auth/login')->post('/auth/login-link', ['email' => $user->email]);
    $signedUrl = URL::signedRoute('login-link.login', ['token' => 'test-token']);
    $loginResponse = $this->get($signedUrl);

    // Assert
    $storeResponse->assertNotFound();
    $loginResponse->assertNotFound();
});
