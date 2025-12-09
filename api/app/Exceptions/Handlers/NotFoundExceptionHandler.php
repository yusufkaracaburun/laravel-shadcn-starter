<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handler for not found exceptions.
 */
final class NotFoundExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof ModelNotFoundException
            || $exception instanceof NotFoundHttpException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        if ($exception instanceof ModelNotFoundException) {
            return 'The requested resource was not found.';
        }

        return sprintf("The requested endpoint '%s' was not found.", $request->getRequestUri());
    }
}
