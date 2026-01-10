<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    /**
     * Create a successful API response.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = Response::HTTP_OK,
        array $extra = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
            'extra'   => $extra,
        ], $code);
    }

    /**
     * Create a OK API response.
     *
     */
    public static function ok(
        mixed $data = null,
    ): JsonResponse {
        return response()->json($data);
    }

    /**
     * Create an error API response.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function error(
        string $message = 'Error',
        int $code = Response::HTTP_BAD_REQUEST,
        mixed $data = null,
        array $extra = [],
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
            'extra'   => $extra,
        ], $code);
    }

    /**
     * Create a created API response.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function created(
        mixed $data = null,
        array $extra = [],
        string $message = 'Created successfully',
    ): JsonResponse {
        return self::success($data, $message, Response::HTTP_CREATED, $extra);
    }

    /**
     * Create a no content API response.
     */
    public static function noContent(?string $message = null, int $code = Response::HTTP_NO_CONTENT): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code'    => $code,
            'message' => $message,
        ], $code);
    }

    /**
     * Create a 404 Not Found error response.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function notFound(
        string $message = 'Resource not found',
        mixed $data = null,
        array $extra = [],
    ): JsonResponse {
        return self::error($message, Response::HTTP_NOT_FOUND, $data, $extra);
    }

    /**
     * Create a 403 Forbidden error response.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function forbidden(
        string $message = 'Forbidden',
        mixed $data = null,
        array $extra = [],
    ): JsonResponse {
        return self::error($message, Response::HTTP_FORBIDDEN, $data, $extra);
    }

    /**
     * Create a 401 Unauthorized error response.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function unauthorized(
        string $message = 'Unauthorized',
        mixed $data = null,
        array $extra = [],
    ): JsonResponse {
        return self::error($message, Response::HTTP_UNAUTHORIZED, $data, $extra);
    }

    /**
     * Create a 422 Validation Error response.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function validationError(
        string $message = 'Validation failed',
        mixed $data = null,
        array $extra = [],
    ): JsonResponse {
        return self::error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $data, $extra);
    }
}
