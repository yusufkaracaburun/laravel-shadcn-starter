<?php

declare(strict_types=1);

use App\Models\OauthConnection;
use App\Models\User;
use Illuminate\Support\Collection;

test('oauth connection can be created', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $connection = OauthConnection::create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '12345',
        'data' => ['name' => 'Test User'],
        'token' => 'access-token',
    ]);

    // Assert
    expect($connection)->toBeInstanceOf(OauthConnection::class);
    expect($connection->user_id)->toBe($user->id);
    expect($connection->provider)->toBe('github');
    expect($connection->provider_id)->toBe('12345');
    expect($connection->token)->toBe('access-token');
});

test('oauth connection has user relationship', function () {
    // Arrange
    $user = User::factory()->create();
    $connection = OauthConnection::create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '12345',
    ]);

    // Act
    $relationship = $connection->user();

    // Assert
    expect($relationship)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    expect($connection->user)->toBeInstanceOf(User::class);
    expect($connection->user->id)->toBe($user->id);
});

test('oauth connection data is cast to collection', function () {
    // Arrange
    $user = User::factory()->create();
    $data = ['name' => 'Test User', 'email' => 'test@example.com'];

    // Act
    $connection = OauthConnection::create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '12345',
        'data' => $data,
    ]);

    // Assert
    expect($connection->data)->toBeInstanceOf(Collection::class);
    expect($connection->data->get('name'))->toBe('Test User');
    expect($connection->data->get('email'))->toBe('test@example.com');
});

test('oauth connection expires_at is cast to datetime', function () {
    // Arrange
    $user = User::factory()->create();
    $expiresAt = now()->addHour();

    // Act
    $connection = OauthConnection::create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '12345',
        'expires_at' => $expiresAt,
    ]);

    // Assert
    expect($connection->expires_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});

test('oauth connection can store refresh token', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $connection = OauthConnection::create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '12345',
        'token' => 'access-token',
        'refresh_token' => 'refresh-token-123',
    ]);

    // Assert
    expect($connection->token)->toBe('access-token');
    expect($connection->refresh_token)->toBe('refresh-token-123');
});

