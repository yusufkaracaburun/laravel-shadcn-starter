<?php

declare(strict_types=1);

use App\Exceptions\OAuthAccountLinkingException;

test('oauth account linking exception email mismatch creates exception with provider', function (): void {
    // Arrange & Act
    $exception = OAuthAccountLinkingException::emailMismatch('github');

    // Assert
    expect($exception)->toBeInstanceOf(OAuthAccountLinkingException::class);
    expect($exception->getMessage())->toContain('github');
    expect($exception->getMessage())->toContain('email');
});

test('oauth account linking exception existing connection creates exception', function (): void {
    // Arrange & Act
    $exception = OAuthAccountLinkingException::existingConnection();

    // Assert
    expect($exception)->toBeInstanceOf(OAuthAccountLinkingException::class);
    expect($exception->getMessage())->toBe(OAuthAccountLinkingException::EXISTING_CONNECTION_ERROR_MESSAGE);
});

test('oauth account linking exception has constant for existing connection message', function (): void {
    // Assert
    expect(OAuthAccountLinkingException::EXISTING_CONNECTION_ERROR_MESSAGE)->toBe('Please login with your existing authentication method.');
});
