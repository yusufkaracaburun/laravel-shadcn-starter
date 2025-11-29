<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ApiResponse
{
    /**
     * Create a successful API response.
     *
     * @param  mixed  $data
     * @param  array<string, mixed>  $extra
     * @param  string  $message
     * @param  int  $code
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
     * @param  string  $message
     * @param  int  $code
     * @param  mixed  $data
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
     * @param  mixed  $data
     * @param  array<string, mixed>  $extra
     * @param  string  $message
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

