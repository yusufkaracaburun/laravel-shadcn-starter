<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handler for general HTTP exceptions.
 */
final class HttpExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof HttpException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        if (!$exception instanceof HttpException) {
            return 500;
        }

        return $exception->getStatusCode();
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        $message = $exception->getMessage();

        return $message === '' || $message === '0' ? 'An HTTP error occurred.' : $message;
    }
}
