<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function (): void {
    // Clear all possible rate limiter variations before each test
    // Match the exact format from FortifyServiceProvider: Str::transliterate(Str::lower($email)).'|'.$ip
    $emails = ['test@example.com', 'john@example.com'];
    $ips = ['127.0.0.1', '::1'];

    foreach ($emails as $email) {
        foreach ($ips as $ip) {
            $throttleKey = Str::transliterate(Str::lower($email)).'|'.$ip;
            RateLimiter::clear('login:'.$throttleKey);
        }
    }
});

test('user can get csrf cookie', function (): void {
    $response = $this->get('/sanctum/csrf-cookie');

    $response->assertNoContent();
});

test('user can login with valid credentials', function (): void {
    $uniqueEmail = 'api-valid-login-'.uniqid().'@example.com';

    $user = User::factory()->create([
        'email' => $uniqueEmail,
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['two_factor']);
});

test('user cannot login with invalid credentials', function (): void {
    $uniqueEmail = 'api-invalid-login-'.uniqid().'@example.com';

    User::factory()->create([
        'email' => $uniqueEmail,
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('user can register with valid data', function (): void {
    $response = $this->postJson('/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);
});

test('user can logout when authenticated', function (): void {
    $user = User::factory()->create();

    // Use actingAs with session for web routes
    $response = $this->actingAs($user)->postJson('/logout');

    $response->assertNoContent();
});

test('user cannot logout when not authenticated', function (): void {
    $response = $this->postJson('/logout');

    $response->assertUnauthorized();
});
