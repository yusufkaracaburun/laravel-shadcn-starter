<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;

trait HandlesPrerequisites
{
    /**
     * Respond with prerequisites data.
     */
    protected function respondWithPrerequisites(array $data): JsonResponse
    {
        return ApiResponse::success($data);
    }
}
