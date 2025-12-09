<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Resources\Json\JsonResource;

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

test('success response handles JsonResource correctly', function (): void {
    $object = (object) ['id' => 1, 'name' => 'Test'];
    $resource = new class($object) extends JsonResource
    {
        public function toArray($request): array
        {
            return [
                'id' => $this->resource->id,
                'name' => $this->resource->name,
            ];
        }
    };

    $response = ApiResponse::success($resource);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect($response->getData(true))->toHaveKeys(['success', 'code', 'message', 'data', 'extra']);
    expect($response->getData(true)['success'])->toBeTrue();
    expect($response->getData(true)['data'])->toBe(['id' => 1, 'name' => 'Test']);
});

test('not found response returns correct structure', function (): void {
    $response = ApiResponse::notFound('Resource not found');

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND);
    expect($response->getData(true)['success'])->toBeFalse();
    expect($response->getData(true)['message'])->toBe('Resource not found');
    expect($response->getData(true)['code'])->toBe(Response::HTTP_NOT_FOUND);
});

test('forbidden response returns correct structure', function (): void {
    $response = ApiResponse::forbidden('Access denied');

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
    expect($response->getData(true)['success'])->toBeFalse();
    expect($response->getData(true)['message'])->toBe('Access denied');
    expect($response->getData(true)['code'])->toBe(Response::HTTP_FORBIDDEN);
});

test('unauthorized response returns correct structure', function (): void {
    $response = ApiResponse::unauthorized('Please login');

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED);
    expect($response->getData(true)['success'])->toBeFalse();
    expect($response->getData(true)['message'])->toBe('Please login');
    expect($response->getData(true)['code'])->toBe(Response::HTTP_UNAUTHORIZED);
});

test('validation error response returns correct structure', function (): void {
    $errors = ['email' => ['The email field is required.']];
    $response = ApiResponse::validationError('Validation failed', $errors);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY);
    expect($response->getData(true)['success'])->toBeFalse();
    expect($response->getData(true)['message'])->toBe('Validation failed');
    expect($response->getData(true)['data'])->toBe($errors);
    expect($response->getData(true)['code'])->toBe(Response::HTTP_UNPROCESSABLE_ENTITY);
});
