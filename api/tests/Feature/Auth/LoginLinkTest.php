<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\LoginLink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use App\Notifications\LoginLinkMail;

test('login link store creates magic link for valid email', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    Notification::fake();
    $user = User::factory()->create(['email' => 'test@example.com']);

    // Act
    $response = $this->withSession([])->post('/auth/login-link', [
        'email' => 'test@example.com',
    ]);

    // Assert
    $response->assertRedirect();
    expect(LoginLink::where('user_id', $user->id)->count())->toBe(1);
    Notification::assertSentTo($user, LoginLinkMail::class);
});

test('login link store validates email is required', function () {
    // Arrange
    Config::set('login-link.enabled', true);

    // Act
    $response = $this->withSession([])->post('/auth/login-link', []);

    // Assert
    $response->assertSessionHasErrors(['email']);
});

test('login link store validates email format', function () {
    // Arrange
    Config::set('login-link.enabled', true);

    // Act
    $response = $this->withSession([])->post('/auth/login-link', [
        'email' => 'invalid-email',
    ]);

    // Assert
    $response->assertSessionHasErrors(['email']);
});

test('login link store validates email exists', function () {
    // Arrange
    Config::set('login-link.enabled', true);

    // Act
    $response = $this->withSession([])->post('/auth/login-link', [
        'email' => 'nonexistent@example.com',
    ]);

    // Assert
    $response->assertSessionHasErrors(['email']);
});

test('login link store respects rate limiting', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    Config::set('login-link.rate_limit_attempts', 1);
    Config::set('login-link.rate_limit_decay', 60);
    $user = User::factory()->create(['email' => 'test@example.com']);
    $key = 'login-link:test@example.com';
    RateLimiter::hit($key, 60);

    // Act
    $response = $this->withSession([])->post('/auth/login-link', [
        'email' => 'test@example.com',
    ]);

    // Assert
    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('login link login authenticates user with valid token', function () {
    // Arrange
    Config::set('login-link.enabled', true);
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
    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
    expect($loginLink->fresh()->used_at)->not->toBeNull();
});

test('login link login fails with expired token', function () {
    // Arrange
    Config::set('login-link.enabled', true);
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
    $response->assertStatus(404);
    expect(Auth::check())->toBeFalse();
});

test('login link login fails with used token', function () {
    // Arrange
    Config::set('login-link.enabled', true);
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
    $response->assertStatus(404);
    expect(Auth::check())->toBeFalse();
});

test('login link login fails with invalid token', function () {
    // Arrange
    Config::set('login-link.enabled', true);
    $signedUrl = URL::signedRoute('login-link.login', ['token' => 'invalid-token-123']);

    // Act
    $response = $this->get($signedUrl);

    // Assert
    $response->assertStatus(404);
    expect(Auth::check())->toBeFalse();
});

test('login link routes return 404 when feature is disabled', function () {
    // Arrange
    Config::set('login-link.enabled', false);
    $user = User::factory()->create();

    // Act
    $storeResponse = $this->withSession([])->post('/auth/login-link', ['email' => $user->email]);
    $signedUrl = URL::signedRoute('login-link.login', ['token' => 'test-token']);
    $loginResponse = $this->get($signedUrl);

    // Assert
    $storeResponse->assertStatus(404);
    $loginResponse->assertStatus(404);
});

