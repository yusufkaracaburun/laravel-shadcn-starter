<?php

declare(strict_types=1);

namespace App\Exceptions\Services;

use Throwable;
use Psr\Log\LogLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for logging exceptions with context and input sanitization.
 * Follows Single Responsibility Principle.
 */
final class ExceptionLoggerService
{
    /**
     * Sensitive fields that should be redacted from logs.
     *
     * @var array<int, string>
     */
    private const array SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'secret',
        'api_key',
        'api_secret',
        'access_token',
        'refresh_token',
        'credit_card',
        'card_number',
        'cvv',
        'ssn',
        'social_security',
    ];

    /**
     * Log an exception with context.
     *
     * @param  array<string, mixed>  $additionalContext
     */
    public function log(
        Throwable $exception,
        Request $request,
        string $level = LogLevel::WARNING,
        array $additionalContext = [],
    ): void {
        $context = array_merge([
            'exception' => $exception::class,
            'message'   => $exception->getMessage(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
            'url'       => $request->fullUrl(),
            'method'    => $request->method(),
            'ip'        => $request->ip(),
            'input'     => $this->sanitizeInput($request->all()),
        ], $additionalContext);

        Log::log($level, $this->getLogMessage($exception), $context);
    }

    /**
     * Get the log message for the exception.
     */
    private function getLogMessage(Throwable $exception): string
    {
        $exceptionType = basename(str_replace('\\', '/', $exception::class));

        return sprintf('API exception: %s', $exceptionType);
    }

    /**
     * Sanitize sensitive input data before logging.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function sanitizeInput(array $input): array
    {
        array_walk_recursive($input, function (&$value, $key): void {
            if (in_array(mb_strtolower((string) $key), self::SENSITIVE_FIELDS, true)) {
                $value = '***REDACTED***';
            }
        });

        return $input;
    }
}
