<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ChatController;

test('index method returns chat data', function (): void {
    $controller = new ChatController();
    $response = $controller->index();

    expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class);
    $data = $response->getData(true);
    expect($data)->toHaveKey('data');
    expect($data['data'])->toHaveKeys(['messages', 'model', 'temperature']);
});

