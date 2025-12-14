<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Interface for exception handlers following the Strategy pattern.
 * Each handler implements this interface to handle a specific exception type.
 */
interface ExceptionHandlerInterface
{
    /**
     * Handle the exception and return a JSON response.
     */
    public function handle(Throwable $exception, Request $request): JsonResponse;

    /**
     * Check if this handler supports the given exception.
     */
    public function supports(Throwable $exception): bool;
}
