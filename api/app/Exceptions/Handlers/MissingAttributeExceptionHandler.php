<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Psr\Log\LogLevel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\MissingAttributeException;

/**
 * Handler for missing attribute exceptions.
 */
final class MissingAttributeExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof MissingAttributeException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        return 'A required attribute is missing. Please contact support.';
    }

    protected function getLogLevel(): string
    {
        return LogLevel::ERROR;
    }
}
