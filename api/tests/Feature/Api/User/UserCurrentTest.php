<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot access current user endpoint', function (): void {
    $response = $this->getJson('/api/user/current');

    $response->assertUnauthorized();
});

test('authenticated user can access current user endpoint', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user/current');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->where('message', 'Success')
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('id', $user->id)
                    ->where('email', $user->email)
                    ->where('name', $user->name)
                    ->etc())
                ->has('extra')
        );
});
