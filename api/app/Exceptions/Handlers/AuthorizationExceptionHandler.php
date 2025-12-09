<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Handler for authorization exceptions.
 */
final class AuthorizationExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof AuthorizationException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        return 'You do not have permission to perform this action.';
    }
}
