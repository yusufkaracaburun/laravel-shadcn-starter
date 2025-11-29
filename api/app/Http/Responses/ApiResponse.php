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
        array $extra = [],
        string $message = 'Success',
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'extra' => $extra,
        ], $code);
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
        array $extra = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'extra' => $extra,
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
        string $message = 'Created successfully'
    ): JsonResponse {
        return self::success($data, $extra, $message, Response::HTTP_CREATED);
    }

    /**
     * Create a no content API response.
     */
    public static function noContent(): Response
    {
        return response()->noContent();
    }
}
