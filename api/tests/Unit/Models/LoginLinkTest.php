<?php

declare(strict_types=1);

use App\Models\LoginLink;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;

test('login link can be created', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $loginLink = LoginLink::create([
        'user_id' => $user->id,
        'token' => 'test-token-123',
        'expires_at' => now()->addMinutes(15),
    ]);

    // Assert
    expect($loginLink)->toBeInstanceOf(LoginLink::class);
    expect($loginLink->user_id)->toBe($user->id);
    expect($loginLink->token)->toBe('test-token-123');
    expect($loginLink->expires_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('login link has user relationship', function () {
    // Arrange
    $user = User::factory()->create();
    $loginLink = LoginLink::create([
        'user_id' => $user->id,
        'token' => 'test-token',
        'expires_at' => now()->addMinutes(15),
    ]);

    // Act
    $relationship = $loginLink->user();

    // Assert
    expect($relationship)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    expect($loginLink->user)->toBeInstanceOf(User::class);
    expect($loginLink->user->id)->toBe($user->id);
});

test('login link expires_at is cast to datetime', function () {
    // Arrange
    $user = User::factory()->create();
    $expiresAt = now()->addMinutes(15);

    // Act
    $loginLink = LoginLink::create([
        'user_id' => $user->id,
        'token' => 'test-token',
        'expires_at' => $expiresAt,
    ]);

    // Assert
    expect($loginLink->expires_at)->toBeInstanceOf(CarbonImmutable::class);
    expect($loginLink->expires_at->format('Y-m-d H:i:s'))->toBe($expiresAt->format('Y-m-d H:i:s'));
});

test('login link used_at is cast to datetime', function () {
    // Arrange
    $user = User::factory()->create();
    $loginLink = LoginLink::create([
        'user_id' => $user->id,
        'token' => 'test-token',
        'expires_at' => now()->addMinutes(15),
    ]);

    // Act
    $loginLink->update(['used_at' => now()]);

    // Assert
    expect($loginLink->used_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('login link prunable returns expired links', function () {
    // Arrange
    $user = User::factory()->create();
    LoginLink::create([
        'user_id' => $user->id,
        'token' => 'expired-token',
        'expires_at' => now()->subDay(),
    ]);
    LoginLink::create([
        'user_id' => $user->id,
        'token' => 'valid-token',
        'expires_at' => now()->addMinutes(15),
    ]);

    // Act
    $prunable = (new LoginLink())->prunable();

    // Assert
    expect($prunable->count())->toBeGreaterThan(0);
    expect($prunable->where('token', 'expired-token')->count())->toBe(1);
    expect($prunable->where('token', 'valid-token')->count())->toBe(0);
});

