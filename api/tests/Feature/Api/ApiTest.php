<?php

declare(strict_types=1);

use Tests\TestCase;

test('api test endpoint returns success', function (): void {
    /** @var TestCase $this */
    $response = $this->getJson('/api/test');

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'API is working!',
    ]);
});

test('api test endpoint returns json', function (): void {
    /** @var TestCase $this */
    $response = $this->getJson('/api/test');

    $response->assertHeader('Content-Type', 'application/json');
});
