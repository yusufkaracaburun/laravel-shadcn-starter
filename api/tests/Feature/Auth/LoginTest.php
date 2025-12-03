<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function (): void {
    // Clear all possible rate limiter variations before each test
    // Match the exact format from FortifyServiceProvider: Str::transliterate(Str::lower($email)).'|'.$ip
    $emails = ['test@example.com', 'nonexistent@example.com', 'invalid-email'];
    $ips = ['127.0.0.1', '::1'];

    foreach ($emails as $email) {
        foreach ($ips as $ip) {
            $throttleKey = Str::transliterate(Str::lower($email)).'|'.$ip;
            RateLimiter::clear('login:'.$throttleKey);
        }
    }
});

test('user can login with valid credentials', function (): void {
    $uniqueEmail = 'valid-login-'.uniqid().'@example.com';

    $user = User::factory()->create([
        'email' => $uniqueEmail,
        'password' => Hash::make('password123'),
    ]);

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'id',
        'name',
        'email',
    ]);

    expect($response->json())->toHaveKey('id');
    expect($response->json('email'))->toBe($uniqueEmail);
});

test('user cannot login with invalid credentials', function (): void {
    $uniqueEmail = 'invalid-login-'.uniqid().'@example.com';

    $user = User::factory()->create([
        'email' => $uniqueEmail,
        'password' => Hash::make('password123'),
    ]);

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('user cannot login with non-existent email', function (): void {
    $uniqueEmail = 'nonexistent-'.uniqid().'@example.com';

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('login requires email field', function (): void {
    // Use a unique email in the request body to avoid rate limiting
    // Even though email is missing, Fortify might still rate limit by IP
    $uniqueEmail = 'no-email-field-'.uniqid().'@example.com';

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('login requires password field', function (): void {
    // Use a unique email to avoid rate limiting from previous tests
    $uniqueEmail = 'password-field-test-'.uniqid().'@example.com';

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/login', [
        'email' => $uniqueEmail,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('login requires valid email format', function (): void {
    // Use a unique invalid email to avoid rate limiting
    $uniqueInvalidEmail = 'invalid-email-'.uniqid();

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/login', [
        'email' => $uniqueInvalidEmail,
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('user can logout when authenticated', function (): void {
    $user = User::factory()->create();

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->actingAs($user)->postJson('/logout');

    $response->assertStatus(204);
});

test('user cannot logout when not authenticated', function (): void {
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/logout');

    $response->assertStatus(401);
});
