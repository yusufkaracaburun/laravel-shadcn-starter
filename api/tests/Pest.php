<?php

declare(strict_types=1);

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Clear login rate limiter for testing.
 * 
 * This matches the exact key format used in FortifyServiceProvider:
 * - If email is provided: Str::transliterate(Str::lower($email)).'|'.$ip
 * - If email is empty: $ip only
 */
function clearLoginRateLimiter(string $email = 'test@example.com', string $ip = '127.0.0.1'): void
{
    $email = trim($email);
    
    if ($email !== '') {
        // Match the exact format from FortifyServiceProvider for non-empty emails
        $throttleKey = Str::transliterate(Str::lower($email)).'|'.$ip;
        RateLimiter::clear('login:'.$throttleKey);
        // Also try IPv6 format
        $throttleKeyV6 = Str::transliterate(Str::lower($email)).'|::1';
        RateLimiter::clear('login:'.$throttleKeyV6);
    } else {
        // For empty emails, rate limiter uses IP only
        RateLimiter::clear('login:'.$ip);
        RateLimiter::clear('login::1');
    }
}
