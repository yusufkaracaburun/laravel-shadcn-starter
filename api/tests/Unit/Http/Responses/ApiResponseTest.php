<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;

test('success response returns correct structure', function (): void {
    $response = ApiResponse::success(['test' => 'data']);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect($response->getData(true))->toHaveKeys(['success', 'code', 'message', 'data', 'extra']);
    expect($response->getData(true)['success'])->toBeTrue();
    expect($response->getData(true)['data'])->toBe(['test' => 'data']);
});

test('error response returns correct structure', function (): void {
    $response = ApiResponse::error('Test error', Response::HTTP_BAD_REQUEST);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect($response->getData(true)['success'])->toBeFalse();
    expect($response->getData(true)['message'])->toBe('Test error');
});

test('created response returns correct status code', function (): void {
    $response = ApiResponse::created(['id' => 1]);

    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED);
    expect($response->getData(true)['success'])->toBeTrue();
});

test('no content response returns correct status code', function (): void {
    $response = ApiResponse::noContent();

    expect($response->getStatusCode())->toBe(Response::HTTP_NO_CONTENT);
});
