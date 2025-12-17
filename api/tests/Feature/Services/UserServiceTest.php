<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\UserServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

test('user service can be resolved from container', function (): void {
    $service = resolve(UserServiceInterface::class);

    expect($service)->toBeInstanceOf(UserServiceInterface::class);
});

test('user service can get all users', function (): void {
    $service = resolve(UserServiceInterface::class);

    $users = $service->all();

    expect($users)->toBeInstanceOf(Collection::class);
});

test('user service can paginate users', function (): void {
    $service = resolve(UserServiceInterface::class);

    $paginated = $service->paginate(10);

    expect($paginated)->toBeInstanceOf(LengthAwarePaginator::class);
});

test('user service can find user by id', function (): void {
    $user = User::factory()->create();

    $service = resolve(UserServiceInterface::class);

    $found = $service->find($user->id);

    expect($found)->toBeInstanceOf(User::class);
    expect($found->id)->toBe($user->id);
});

test('user service can create a user', function (): void {
    $service = resolve(UserServiceInterface::class);

    $user = $service->create([
        'name'     => 'Test User',
        'email'    => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});

test('user service can update a user', function (): void {
    $user = User::factory()->create();

    $service = resolve(UserServiceInterface::class);

    $updated = $service->update($user->id, [
        'name' => 'Updated Name',
    ]);

    expect($updated)->toBeInstanceOf(User::class);
    expect($updated->name)->toBe('Updated Name');
});

test('user service can delete a user', function (): void {
    $user = User::factory()->create();

    $service = resolve(UserServiceInterface::class);

    $deleted = $service->delete($user->id);

    expect($deleted)->toBeTrue();
    expect($service->exists($user->id))->toBeFalse();
});

test('user service can check if user exists', function (): void {
    $user = User::factory()->create();

    $service = resolve(UserServiceInterface::class);

    expect($service->exists($user->id))->toBeTrue();
    expect($service->exists(99999))->toBeFalse();
});

test('user service has repository injected', function (): void {
    $service = resolve(UserServiceInterface::class);

    $repository = $service->getRepository();

    expect($repository)->toBeInstanceOf(UserRepositoryInterface::class);
});
