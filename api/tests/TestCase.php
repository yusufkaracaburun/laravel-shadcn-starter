<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear all login rate limiters before each test
        // This ensures rate limiting doesn't interfere with tests
        $this->clearLoginRateLimiters();
    }

    /**
     * Clear all login rate limiters to prevent test interference.
     *
     * This matches the exact key format used in FortifyServiceProvider:
     * - If email is provided: Str::transliterate(Str::lower($email)).'|'.$ip
     * - If email is empty: $ip only
     */
    protected function clearLoginRateLimiters(): void
    {
        // Clear rate limiters for common test emails and IPs
        $emails = ['test@example.com', 'john@example.com', 'nonexistent@example.com', 'invalid-email', 'test8@example.com', 'testlogin@example.com', 'testwrong@example.com'];
        $ips = ['127.0.0.1', '::1'];

        foreach ($emails as $email) {
            foreach ($ips as $ip) {
                // Match the exact format from FortifyServiceProvider for non-empty emails
                $throttleKey = Str::transliterate(Str::lower($email)).'|'.$ip;
                RateLimiter::clear('login:'.$throttleKey);
            }
        }

        // Also clear IP-only rate limiters (for empty email cases)
        foreach ($ips as $ip) {
            RateLimiter::clear('login:'.$ip);
        }
    }
}
