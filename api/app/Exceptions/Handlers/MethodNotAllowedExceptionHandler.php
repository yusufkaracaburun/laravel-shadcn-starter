<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Handler for method not allowed exceptions.
 */
final class MethodNotAllowedExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof MethodNotAllowedHttpException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        return Response::HTTP_METHOD_NOT_ALLOWED;
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        return sprintf('The %s method is not allowed for this endpoint.', $request->method());
    }

    protected function getExtraData(Throwable $exception, Request $request): array
    {
        if (!$exception instanceof MethodNotAllowedHttpException) {
            return [];
        }

        $headers = $exception->getHeaders();
        $allowedMethods = $headers['Allow'] ?? 'Unknown';

        return ['allowed_methods' => $allowedMethods];
    }
}
