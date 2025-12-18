<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;

/**
 * Base test case for all tests.
 *
 * @mixin MakesHttpRequests
 *
 * @method TestResponse get(string $uri, array $headers = [])
 * @method TestResponse post(string $uri, array $data = [], array $headers = [])
 * @method TestResponse put(string $uri, array $data = [], array $headers = [])
 * @method TestResponse patch(string $uri, array $data = [], array $headers = [])
 * @method TestResponse delete(string $uri, array $data = [], array $headers = [])
 * @method TestResponse getJson(string $uri, array $headers = [])
 * @method TestResponse postJson(string $uri, array $data = [], array $headers = [])
 * @method TestResponse putJson(string $uri, array $data = [], array $headers = [])
 * @method TestResponse patchJson(string $uri, array $data = [], array $headers = [])
 * @method TestResponse deleteJson(string $uri, array $data = [], array $headers = [])
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear all login rate limiters before each test
        // This ensures rate limiting doesn't interfere with tests
        $this->clearLoginRateLimiters();

        // Ensure database is clean for parallel testing
        $this->ensureCleanDatabase();
    }

    /**
     * Clean up after each test to ensure complete isolation.
     * This ensures no data persists between tests in parallel execution.
     */
    protected function tearDown(): void
    {
        // Clean up all test data after each test
        // This ensures complete isolation for parallel testing
        $this->cleanUsers();

        parent::tearDown();
    }

    /**
     * Clean up users after database is refreshed.
     * This ensures no data persists between tests in parallel execution.
     */
    protected function afterRefreshingDatabase()
    {
        // Clean up users after RefreshDatabase has run migrations
        // This is critical for parallel testing where databases might share state
        $this->cleanUsers();
    }

    /**
     * Ensure the database is clean before each test.
     * This is especially important for parallel testing where databases might be shared.
     */
    protected function ensureCleanDatabase(): void
    {
        // For SQLite, ensure foreign keys are enabled and database is clean
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }

    /**
     * Clean up all users and related data for test isolation.
     * This ensures parallel tests don't interfere with each other.
     *
     * This method is called both after RefreshDatabase runs and in tearDown
     * to ensure complete isolation between tests.
     */
    protected function cleanUsers(): void
    {
        // Disable foreign key checks temporarily for SQLite to allow deletion in any order
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
        }

        try {
            // Delete all pivot table entries first
            DB::table('team_user')->delete();

            // Delete teams
            Team::query()->delete();

            // Delete all users (including soft-deleted ones)
            User::withTrashed()->forceDelete();

            // Clean up any other related tables that might have test data
            // Add more cleanup here as needed for other models
        } finally {
            // Re-enable foreign key checks
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys=ON');
            }
        }
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
                $throttleKey = Str::transliterate(Str::lower($email)) . '|' . $ip;
                RateLimiter::clear('login:' . $throttleKey);
            }
        }

        // Also clear IP-only rate limiters (for empty email cases)
        foreach ($ips as $ip) {
            RateLimiter::clear('login:' . $ip);
        }
    }
}
