<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

test('unauthenticated users cannot delete users', function (): void {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/user/{$user->id}");

    $response->assertUnauthorized();
});

test('authenticated user can delete user', function (): void {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->deleteJson("/api/user/{$targetUser->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
});

test('user can view specific user by id', function (): void {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/{$targetUser->id}");

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn ($json) => $json->where('id', $targetUser->id)
                    ->where('email', $targetUser->email)
                    ->etc())
                ->has('extra')
                ->etc()
        );
});

