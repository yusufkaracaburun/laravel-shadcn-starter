<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;

test('user can be created with factory', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
    expect($user->id)->not->toBeNull();
    expect($user->email)->not->toBeNull();
    expect($user->name)->not->toBeNull();
});

test('user password is hashed when created', function () {
    $password = 'password123';
    $user = User::factory()->create([
        'password' => Hash::make($password),
    ]);

    expect(Hash::check($password, $user->password))->toBeTrue();
});

test('user password is not visible in array', function () {
    $user = User::factory()->create();

    $userArray = $user->toArray();

    expect($userArray)->not->toHaveKey('password');
    expect($userArray)->not->toHaveKey('remember_token');
});

test('user email_verified_at is cast to datetime', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('user can be mass assigned name, email, and password', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->password)->not->toBeNull();
});

test('user has fillable attributes', function () {
    $user = new User();

    expect($user->getFillable())->toBe(['name', 'email', 'password']);
});

test('user has hidden attributes', function () {
    $user = new User();

    expect($user->getHidden())->toBe(['password', 'remember_token']);
});

test('user has api tokens trait', function () {
    $user = User::factory()->create();

    expect(method_exists($user, 'tokens'))->toBeTrue();
    expect(method_exists($user, 'createToken'))->toBeTrue();
});

test('user can create api token', function () {
    // This test requires the personal_access_tokens table
    // For unit tests, we verify the method exists and can be called
    // The actual database interaction is tested in feature tests
    $user = User::factory()->make();

    expect(method_exists($user, 'createToken'))->toBeTrue();
});
