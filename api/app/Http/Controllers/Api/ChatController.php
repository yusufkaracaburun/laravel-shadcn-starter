<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;

final class ChatController extends Controller
{
    /**
     * Get chat data.
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        $data = [
            'messages' => [],
            'model' => 'default',
            'temperature' => 0.7,
        ];

        return ApiResponse::success($data);
    }
}
