<?php

declare(strict_types=1);

namespace App\Exceptions\Services;

use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;

/**
 * Service responsible for building standardized exception responses.
 * Uses the existing ApiResponse class for consistency.
 * Follows Single Responsibility Principle.
 */
final class ExceptionResponseBuilder
{
    /**
     * Build a standardized error response.
     *
     * @param  array<string, mixed>  $extra
     */
    public function build(int $statusCode, string $message, array $extra = []): JsonResponse
    {
        return ApiResponse::error($message, $statusCode, null, $extra);
    }
}
