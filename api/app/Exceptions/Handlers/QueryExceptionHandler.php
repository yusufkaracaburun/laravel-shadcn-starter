<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Psr\Log\LogLevel;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handler for database query exceptions.
 */
final class QueryExceptionHandler extends AbstractExceptionHandler
{
    public function supports(Throwable $exception): bool
    {
        return $exception instanceof QueryException;
    }

    protected function getStatusCode(Throwable $exception): int
    {
        if (! $exception instanceof QueryException) {
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $errorCode = $exception->errorInfo[1] ?? null;

        return match ($errorCode) {
            1451, 1062 => Response::HTTP_CONFLICT, // Foreign key constraint or duplicate entry
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };
    }

    protected function getMessage(Throwable $exception, Request $request): string
    {
        if (! $exception instanceof QueryException) {
            return 'A database error occurred. Please try again later.';
        }

        $errorCode = $exception->errorInfo[1] ?? null;

        return match ($errorCode) {
            1451 => 'Cannot delete this resource because it is referenced by other records.',
            1062 => 'A record with this information already exists.',
            default => 'A database error occurred. Please try again later.',
        };
    }

    protected function getLogContext(Throwable $exception, Request $request): array
    {
        if (! $exception instanceof QueryException) {
            return [];
        }

        return ['sql' => $exception->getSql()];
    }

    protected function getLogLevel(): string
    {
        return LogLevel::ERROR;
    }
}
