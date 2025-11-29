<?php

declare(strict_types=1);

use App\Http\Controllers\Api\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

test('current method returns authenticated user', function (): void {
    $user = User::factory()->create();
    Auth::shouldReceive('user')->once()->andReturn($user);

    $controller = new UserController();
    $response = $controller->current();

    expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
});

test('index method returns all users', function (): void {
    User::factory()->count(3)->create();

    $controller = new UserController();
    $response = $controller->index();

    expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
});

test('show method returns specific user', function (): void {
    $user = User::factory()->create();

    $controller = new UserController();
    $response = $controller->show($user);

    expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
});

