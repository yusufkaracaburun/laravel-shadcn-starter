<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handler for invalid argument exceptions.
 */
final class InvalidArgumentExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof InvalidArgumentException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        $message = $exception->getMessage();

        return $message === '' || $message === '0' ? 'Invalid argument provided.' : $message;
    }
}
