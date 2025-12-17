<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;
use Psr\Log\LogLevel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exceptions\Services\ExceptionLoggerService;
use App\Exceptions\Services\ExceptionResponseBuilder;

/**
 * Abstract exception handler implementing Template Method pattern.
 * Defines the algorithm for exception handling, allowing subclasses to implement specific details.
 */
abstract class AbstractExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        protected ExceptionLoggerService $logger,
        protected ExceptionResponseBuilder $responseBuilder,
    ) {}

    /**
     * Get the HTTP status code for the exception.
     */
    abstract protected function getStatusCode(Throwable $exception): int;

    /**
     * Get the error message for the exception.
     */
    abstract protected function getMessage(Throwable $exception, Request $request): string;

    /**
     * Template method - defines the algorithm for handling exceptions.
     */
    final public function handle(Throwable $exception, Request $request): JsonResponse
    {
        $this->logException($exception, $request);

        return $this->buildResponse($exception, $request);
    }

    /**
     * Get extra data to include in the response.
     *
     * @return array<string, mixed>
     */
    protected function getExtraData(Throwable $exception, Request $request): array
    {
        return [];
    }

    /**
     * Get the log level for this exception type.
     */
    protected function getLogLevel(): string
    {
        return LogLevel::WARNING;
    }

    /**
     * Log the exception.
     */
    protected function logException(Throwable $exception, Request $request): void
    {
        $this->logger->log(
            $exception,
            $request,
            $this->getLogLevel(),
            $this->getLogContext($exception, $request),
        );
    }

    /**
     * Get additional context for logging.
     *
     * @return array<string, mixed>
     */
    protected function getLogContext(Throwable $exception, Request $request): array
    {
        return [];
    }

    /**
     * Build the response for the exception.
     */
    protected function buildResponse(Throwable $exception, Request $request): JsonResponse
    {
        return $this->responseBuilder->build(
            $this->getStatusCode($exception),
            $this->getMessage($exception, $request),
            $this->getExtraData($exception, $request),
        );
    }
}
