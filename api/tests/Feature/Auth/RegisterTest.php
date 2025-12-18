<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can register with valid data', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'                  => 'John Doe',
        'email'                 => 'john@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name'  => 'John Doe',
    ]);

    $user = User::query()->where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('user cannot register with duplicate email', function (): void {
    /** @var TestCase $this */
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'                  => 'John Doe',
        'email'                 => 'existing@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('register requires name field', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'email'                 => 'john@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('register requires email field', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'                  => 'John Doe',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('register requires password field', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'                  => 'John Doe',
        'email'                 => 'john@example.com',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('register requires password confirmation', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('register requires matching password confirmation', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'                  => 'John Doe',
        'email'                 => 'john@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('register requires valid email format', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'                  => 'John Doe',
        'email'                 => 'invalid-email',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('register requires minimum password length', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'                  => 'John Doe',
        'email'                 => 'john@example.com',
        'password'              => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('registered user can login immediately', function (): void {
    /** @var TestCase $this */
    // Get CSRF cookie first
    $this->get('/sanctum/csrf-cookie');

    $response = $this->postJson('/register', [
        'name'                  => 'John Doe',
        'email'                 => 'john@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);

    $loginResponse = $this->postJson('/login', [
        'email'    => 'john@example.com',
        'password' => 'password123',
    ]);

    // Fortify may return 200 with JSON or 302 redirect depending on configuration
    expect($loginResponse->status())->toBeIn([200, 302]);
});
