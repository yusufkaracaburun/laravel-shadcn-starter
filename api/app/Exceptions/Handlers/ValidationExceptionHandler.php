<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handler for validation exceptions.
 */
final class ValidationExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof ValidationException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        return 'The provided data is invalid.';
    }

    protected function getExtraData(Throwable $exception, Request $request): array
    {
        return [];
    }

    /**
     * Override buildResponse to use Laravel's validation error format.
     */
    protected function buildResponse(Throwable $exception, Request $request): JsonResponse
    {
        if (! $exception instanceof ValidationException) {
            return parent::buildResponse($exception, $request);
        }

        // Use Laravel's default validation error format for compatibility with tests
        return response()->json([
            'success' => false,
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $this->getMessage($exception, $request),
            'data' => null,
            'extra' => [],
            'errors' => $exception->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function getLogContext(Throwable $exception, Request $request): array
    {
        if (! $exception instanceof ValidationException) {
            return [];
        }

        $errors = [];
        foreach ($exception->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = [
                    'field' => $field,
                    'message' => $message,
                ];
            }
        }

        return ['errors' => $errors];
    }
}
