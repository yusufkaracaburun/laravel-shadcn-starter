<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\LogRecord;

/**
 * Monolog processor that sanitizes sensitive fields from log records.
 *
 * Automatically redacts sensitive information like passwords, tokens,
 * and API keys to prevent them from being written to log files.
 */
final class SanitizeProcessor
{
    /**
     * List of sensitive field names to redact.
     *
     * @var list<string>
     */
    private array $sensitiveFields = [
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
     * Process the log record and sanitize sensitive fields.
     *
     * @param  LogRecord  $record  The log record to process
     * @return LogRecord The processed log record with sensitive data redacted
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        $extra = $record->extra;
        $sanitizedContext = is_array($context) ? $this->sanitize($context) : $context;
        $sanitizedExtra = is_array($extra) ? $this->sanitize($extra) : $extra;

        return $record->with(context: $sanitizedContext, extra: $sanitizedExtra);
    }

    /**
     * Recursively sanitize sensitive fields in an array.
     *
     * @param  array<string, mixed>  $data  The data array to sanitize
     * @return array<string, mixed> The sanitized data array
     */
    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->sensitiveFields, true)) {
                $data[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }

        return $data;
    }
}
