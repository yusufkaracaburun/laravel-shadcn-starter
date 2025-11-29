<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\OauthConnection;
use Laravel\Socialite\Two\User as SocialiteUser;
use App\Jobs\User\UpdateUserProfileInformationJob;

test('update user profile information job creates oauth connection', function () {
    // Arrange
    $user = User::factory()->create();
    $socialiteUser = new SocialiteUser();
    $socialiteUser->map([
        'id' => '12345',
        'name' => 'Test User',
        'email' => $user->email,
        'avatar' => 'https://example.com/avatar.jpg',
    ]);
    $socialiteUser->token = 'access-token';
    $socialiteUser->refreshToken = 'refresh-token';
    $socialiteUser->expiresIn = 3600;

    $job = new UpdateUserProfileInformationJob($user, $socialiteUser, 'github');

    // Act
    $job->handle();

    // Assert
    $connection = OauthConnection::where('user_id', $user->id)
        ->where('provider', 'github')
        ->first();

    expect($connection)->not->toBeNull();
    expect($connection->provider_id)->toBe('12345');
    expect($connection->token)->toBe('access-token');
    expect($connection->refresh_token)->toBe('refresh-token');
});

test('update user profile information job updates existing oauth connection', function () {
    // Arrange
    $user = User::factory()->create();
    OauthConnection::create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => '12345',
        'token' => 'old-token',
    ]);

    $socialiteUser = new SocialiteUser();
    $socialiteUser->map([
        'id' => '12345',
        'name' => 'Updated User',
        'email' => $user->email,
        'avatar' => null,
    ]);
    $socialiteUser->token = 'new-token';
    $socialiteUser->refreshToken = null;
    $socialiteUser->expiresIn = null;

    $job = new UpdateUserProfileInformationJob($user, $socialiteUser, 'github');

    // Act
    $job->handle();

    // Assert
    $connection = OauthConnection::where('user_id', $user->id)
        ->where('provider', 'github')
        ->first();

    expect($connection->token)->toBe('new-token');
});

test('update user profile information job sets profile photo path when avatar exists', function () {
    // Arrange
    $user = User::factory()->create();
    $socialiteUser = new SocialiteUser();
    $socialiteUser->map([
        'id' => '12345',
        'name' => 'Test User',
        'email' => $user->email,
        'avatar' => 'https://example.com/avatar.jpg',
    ]);
    $socialiteUser->token = 'token';
    $socialiteUser->refreshToken = null;
    $socialiteUser->expiresIn = null;

    $job = new UpdateUserProfileInformationJob($user, $socialiteUser, 'github');

    // Act
    $job->handle();

    // Assert
    $user->refresh();
    expect($user->profile_photo_path)->toBe('https://example.com/avatar.jpg');
});

test('update user profile information job verifies email when not verified', function () {
    // Arrange
    $user = User::factory()->create(['email_verified_at' => null]);
    $socialiteUser = new SocialiteUser();
    $socialiteUser->map([
        'id' => '12345',
        'name' => 'Test User',
        'email' => $user->email,
        'avatar' => null,
    ]);
    $socialiteUser->token = 'token';
    $socialiteUser->refreshToken = null;
    $socialiteUser->expiresIn = null;

    $job = new UpdateUserProfileInformationJob($user, $socialiteUser, 'github');

    // Act
    $job->handle();

    // Assert
    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});

test('update user profile information job does not overwrite existing email verification', function () {
    // Arrange
    $originalVerifiedAt = now()->subDay();
    $user = User::factory()->create(['email_verified_at' => $originalVerifiedAt]);
    $socialiteUser = new SocialiteUser();
    $socialiteUser->map([
        'id' => '12345',
        'name' => 'Test User',
        'email' => $user->email,
        'avatar' => null,
    ]);
    $socialiteUser->token = 'token';
    $socialiteUser->refreshToken = null;
    $socialiteUser->expiresIn = null;

    $job = new UpdateUserProfileInformationJob($user, $socialiteUser, 'github');

    // Act
    $job->handle();

    // Assert
    $user->refresh();
    expect($user->email_verified_at->format('Y-m-d H:i:s'))->toBe($originalVerifiedAt->format('Y-m-d H:i:s'));
});
