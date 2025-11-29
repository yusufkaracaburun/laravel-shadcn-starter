<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot access user index endpoint', function (): void {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

test('authenticated user can list all users', function (): void {
    $user = User::factory()->create();
    User::factory()->count(2)->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', 3)
                ->has('data.0', fn (AssertableJson $json): AssertableJson => $json->has('id')
                    ->has('name')
                    ->has('email')
                    ->etc())
                ->has('extra')
                ->etc()
        );
});
