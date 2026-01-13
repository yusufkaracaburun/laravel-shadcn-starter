<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Users\UserResource;
use App\Http\Controllers\Api\UserController;
use App\Http\Resources\Users\UserCollection;
use App\Http\Requests\Users\UserIndexRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Contracts\UserServiceInterface;

test('current method returns authenticated user', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $user->load(['teams', 'currentTeam', 'ownedTeams']);

    $userResource = new UserResource($user);

    $service = $this->mock(UserServiceInterface::class);
    $service->shouldReceive('getCurrentUser')
        ->once()
        ->with($user)
        ->andReturn($userResource);

    Auth::shouldReceive('user')->once()->andReturn($user);

    $controller = new UserController($service);
    $response = $controller->current();

    expect($response)->toBeInstanceOf(JsonResponse::class);
});

test('index method returns all users', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $paginator = new LengthAwarePaginator([], 0, 15, 1);
    $collection = new UserCollection($paginator);

    $service = $this->mock(UserServiceInterface::class);
    $service->shouldReceive('getPaginated')
        ->once()
        ->with(15, null)
        ->andReturn($collection);

    Auth::shouldReceive('user')->once()->andReturn($user);

    $request = UserIndexRequest::create('/api/user', 'GET', ['per_page' => 15]);
    $request->setContainer(app());
    $request->validateResolved();

    $controller = new UserController($service);
    $response = $controller->index($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);
});

test('show method returns specific user', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $user->load(['teams', 'currentTeam', 'ownedTeams']);

    $currentUser = User::factory()->create();

    $userResource = new UserResource($user);

    $service = $this->mock(UserServiceInterface::class);
    $service->shouldReceive('findById')
        ->once()
        ->with($user->id, null)
        ->andReturn($userResource);

    Auth::shouldReceive('user')->once()->andReturn($currentUser);

    $controller = new UserController($service);
    $response = $controller->show($user);

    expect($response)->toBeInstanceOf(JsonResponse::class);
});
