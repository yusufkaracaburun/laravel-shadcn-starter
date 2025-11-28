<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'two_factor',
    ]);

    expect($response->json())->toHaveKey('two_factor');
    expect($response->json('two_factor'))->toBeFalse();
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('user cannot login with non-existent email', function () {
    $response = $this->postJson('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('login requires email field', function () {
    $response = $this->postJson('/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('login requires password field', function () {
    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('login requires valid email format', function () {
    $response = $this->postJson('/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('user can logout when authenticated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/logout');

    $response->assertStatus(204);
});

test('user cannot logout when not authenticated', function () {
    $response = $this->postJson('/logout');

    $response->assertStatus(401);
});
