<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Throwable;

/**
 * Factory for resolving appropriate exception handlers.
 * Implements Factory pattern to map exception types to their handlers.
 */
final class ExceptionHandlerFactory
{
    /**
     * Registered exception handlers.
     *
     * @var array<string, ExceptionHandlerInterface>
     */
    private array $handlers = [];

    /**
     * Register an exception handler for a specific exception class.
     */
    public function register(string $exceptionClass, ExceptionHandlerInterface $handler): void
    {
        $this->handlers[$exceptionClass] = $handler;
    }

    /**
     * Get the appropriate handler for the given exception.
     */
    public function getHandler(Throwable $exception): ?ExceptionHandlerInterface
    {
        // First, try exact class match
        $exceptionClass = $exception::class;
        if (isset($this->handlers[$exceptionClass])) {
            $handler = $this->handlers[$exceptionClass];
            if ($handler->supports($exception)) {
                return $handler;
            }
        }

        // Then, try parent class matches
        foreach ($this->handlers as $exceptionClass => $handler) {
            if ($exception instanceof $exceptionClass && $handler->supports($exception)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Get all registered handlers.
     *
     * @return array<string, ExceptionHandlerInterface>
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
