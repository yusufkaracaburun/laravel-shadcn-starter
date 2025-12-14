<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserIndexRequest;
use App\Http\Controllers\Api\UserController;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\UserRepositoryInterface;

test('current method returns authenticated user', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $user->load(['teams', 'currentTeam', 'ownedTeams']);

    $repository = $this->mock(UserRepositoryInterface::class);
    $repository->shouldReceive('getCurrentUser')
        ->once()
        ->with($user)
        ->andReturn($user);

    Auth::shouldReceive('user')->once()->andReturn($user);

    $controller = new UserController($repository);
    $response = $controller->current();

    expect($response)->toBeInstanceOf(JsonResponse::class);
});

test('index method returns all users', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $paginator = new LengthAwarePaginator([], 0, 15, 1);

    $repository = $this->mock(UserRepositoryInterface::class);
    $repository->shouldReceive('getPaginated')
        ->once()
        ->with(15, null)
        ->andReturn($paginator);

    Auth::shouldReceive('user')->once()->andReturn($user);

    $request = UserIndexRequest::create('/api/user', 'GET', ['per_page' => 15]);
    $request->setContainer(app());
    $request->validateResolved();

    $controller = new UserController($repository);
    $response = $controller->index($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);
});

test('show method returns specific user', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $user->load(['teams', 'currentTeam', 'ownedTeams']);

    $currentUser = User::factory()->create();

    $repository = $this->mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->once()
        ->with($user->id, null)
        ->andReturn($user);

    Auth::shouldReceive('user')->once()->andReturn($currentUser);

    $controller = new UserController($repository);
    $response = $controller->show($user);

    expect($response)->toBeInstanceOf(JsonResponse::class);
});
