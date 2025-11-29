<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot access chat data endpoint', function (): void {
    $response = $this->getJson('/api/chat/data');

    $response->assertUnauthorized();
});

test('authenticated user can access chat data endpoint', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/chat/data');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->has('messages')
                    ->has('model')
                    ->has('temperature')
                    ->etc())
                ->has('extra')
                ->etc()
        );
});
