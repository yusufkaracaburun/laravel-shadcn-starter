<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

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

