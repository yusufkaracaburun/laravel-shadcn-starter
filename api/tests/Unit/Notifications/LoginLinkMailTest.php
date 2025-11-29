<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\LoginLink;
use App\Notifications\LoginLinkMail;
use Illuminate\Support\Facades\Notification;

test('login link mail notification uses mail channel', function (): void {
    // Arrange
    $user = User::factory()->create();
    $loginLink = \App\Models\LoginLink::query()->create([
        'user_id' => $user->id,
        'token' => 'test-token-123',
        'expires_at' => now()->addMinutes(15),
    ]);
    $notification = new LoginLinkMail($loginLink);

    // Act
    $channels = $notification->via();

    // Assert
    expect($channels)->toBeArray();
    expect($channels)->toContain('mail');
});

test('login link mail notification has correct subject', function (): void {
    // Arrange
    $user = User::factory()->create();
    $loginLink = \App\Models\LoginLink::query()->create([
        'user_id' => $user->id,
        'token' => 'test-token-123',
        'expires_at' => now()->addMinutes(15),
    ]);
    $notification = new LoginLinkMail($loginLink);

    // Act
    $mailMessage = $notification->toMail();

    // Assert
    expect($mailMessage->subject)->toBe('Your Magic Login Link');
});

test('login link mail notification contains login link url', function (): void {
    // Arrange
    $user = User::factory()->create();
    $loginLink = \App\Models\LoginLink::query()->create([
        'user_id' => $user->id,
        'token' => 'test-token-123',
        'expires_at' => now()->addMinutes(15),
    ]);
    $notification = new LoginLinkMail($loginLink);

    // Act
    $mailMessage = $notification->toMail();

    // Assert
    expect($mailMessage->actionUrl)->toContain('login-link');
    expect($mailMessage->actionUrl)->toContain('test-token-123');
});

test('login link mail notification can be sent to user', function (): void {
    // Arrange
    Notification::fake();
    $user = User::factory()->create();
    $loginLink = \App\Models\LoginLink::query()->create([
        'user_id' => $user->id,
        'token' => 'test-token-123',
        'expires_at' => now()->addMinutes(15),
    ]);

    // Act
    $user->notify(new LoginLinkMail($loginLink));

    // Assert
    Notification::assertSentTo($user, LoginLinkMail::class);
});
