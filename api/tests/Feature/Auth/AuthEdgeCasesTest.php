<?php

declare(strict_types=1);

test('user cannot login with empty email', function () {
    $response = $this->postJson('/login', [
        'email' => '',
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('user cannot login with empty password', function () {
    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('user cannot register with empty name', function () {
    $response = $this->postJson('/register', [
        'name' => '',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('user cannot register with name exceeding max length', function () {
    $response = $this->postJson('/register', [
        'name' => str_repeat('a', 256),
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('user cannot register with email exceeding max length', function () {
    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => str_repeat('a', 250).'@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('user cannot register with password less than 8 characters', function () {
    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('user can register with exactly 8 character password', function () {
    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'test8@example.com',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]);

    $response->assertStatus(201);
});

test('user can login after registration with correct credentials', function () {
    $registerResponse = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'testlogin@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $registerResponse->assertStatus(201);

    $loginResponse = $this->postJson('/login', [
        'email' => 'testlogin@example.com',
        'password' => 'password123',
    ]);

    expect($loginResponse->status())->toBeIn([200, 302]);
});

test('user cannot login after registration with wrong password', function () {
    $registerResponse = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'testwrong@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $registerResponse->assertStatus(201);

    $loginResponse = $this->postJson('/login', [
        'email' => 'testwrong@example.com',
        'password' => 'wrongpassword',
    ]);

    // Fortify may return 422 with validation errors or 302 redirect
    expect($loginResponse->status())->toBeIn([422, 302]);

    if ($loginResponse->status() === 422) {
        $loginResponse->assertJsonValidationErrors(['email']);
    }
});
