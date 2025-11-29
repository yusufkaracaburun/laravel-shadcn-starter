<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Validation\ValidationException;

test('create new user action creates user with valid data', function (): void {
    $action = new CreateNewUser();

    $user = $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('John Doe');
    expect($user->email)->toBe('john@example.com');
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('create new user action validates name is required', function (): void {
    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]))->toThrow(ValidationException::class);
});

test('create new user action validates email is required', function (): void {
    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'name' => 'John Doe',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]))->toThrow(ValidationException::class);
});

test('create new user action validates email format', function (): void {
    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]))->toThrow(ValidationException::class);
});

test('create new user action validates email is unique', function (): void {
    User::factory()->create(['email' => 'existing@example.com']);

    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]))->toThrow(ValidationException::class);
});

test('create new user action validates password is required', function (): void {
    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password_confirmation' => 'password123',
    ]))->toThrow(ValidationException::class);
});

test('create new user action validates password minimum length', function (): void {
    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]))->toThrow(ValidationException::class);
});

test('create new user action validates password confirmation', function (): void {
    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ]))->toThrow(ValidationException::class);
});

test('create new user action validates name max length', function (): void {
    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'name' => str_repeat('a', 256),
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]))->toThrow(ValidationException::class);
});

test('create new user action validates email max length', function (): void {
    $action = new CreateNewUser();

    expect(fn (): User => $action->create([
        'name' => 'John Doe',
        'email' => str_repeat('a', 250).'@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]))->toThrow(ValidationException::class);
});
