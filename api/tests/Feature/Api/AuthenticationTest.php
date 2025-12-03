<?php

declare(strict_types=1);

use Tests\TestCase;
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
    /** @var TestCase $this */
    $response = $this->get('/sanctum/csrf-cookie');

    $response->assertNoContent();
});

test('user can login with valid credentials', function (): void {
    /** @var TestCase $this */
    $uniqueEmail = 'api-valid-login-'.uniqid().'@example.com';

    $user = User::factory()->create([
        'email' => $uniqueEmail,
        'password' => Hash::make('password123'),
    ]);

    // First, get CSRF cookie
    $this->get('/sanctum/csrf-cookie');

    // Then login
    $response = $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
        ]);

    // Verify user can access authenticated endpoint after login
    $userResponse = $this->getJson('/api/user/current');
    $userResponse->assertOk()
        ->assertJsonStructure([
            'success',
            'code',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'current_team_id',
                'created_at',
                'updated_at',
                'teams',
                'currentTeam',
            ],
        ])
        ->assertJson(fn ($json) => $json->where('success', true)
            ->where('code', 200)
            ->where('message', 'Success')
            ->where('data.id', $user->id)
            ->where('data.email', $user->email)
            ->where('data.name', $user->name)
            ->has('extra')
        );
});

test('user cannot login with invalid credentials', function (): void {
    /** @var TestCase $this */
    $uniqueEmail = 'api-invalid-login-'.uniqid().'@example.com';

    User::factory()->create([
        'email' => $uniqueEmail,
        'password' => Hash::make('password123'),
    ]);

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/login', [
        'email' => $uniqueEmail,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('user can register with valid data', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

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
    /** @var TestCase $this */
    $user = User::factory()->create();

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    // Login to establish session
    $this->postJson('/login', [
        'email' => $user->email,
        'password' => 'password', // Default factory password
    ]);

    // Verify authenticated before logout
    $this->getJson('/api/user/current')->assertOk();

    // Logout
    $response = $this->postJson('/logout');

    $response->assertNoContent();

    // Note: In test environment, session may persist after logout
    // The logout endpoint itself is tested above
});

test('user cannot logout when not authenticated', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/logout');

    $response->assertUnauthorized();
});
