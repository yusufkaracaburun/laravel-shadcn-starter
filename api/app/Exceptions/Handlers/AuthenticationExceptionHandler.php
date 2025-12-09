<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Handler for authentication exceptions.
 */
final class AuthenticationExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof AuthenticationException
            || $exception instanceof AccessDeniedHttpException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof AccessDeniedHttpException) {
            return Response::HTTP_FORBIDDEN;
        }

        return Response::HTTP_UNAUTHORIZED;
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        if ($exception instanceof AccessDeniedHttpException) {
            return 'You do not have permission to perform this action.';
        }

        return 'Authentication required. Please provide valid credentials.';
    }
}
