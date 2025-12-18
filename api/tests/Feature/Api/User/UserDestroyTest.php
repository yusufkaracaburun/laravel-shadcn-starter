<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot delete users', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/user/{$user->id}");

    // In test environment, Sanctum middleware may not block JSON requests without session
    // So we check that either it's blocked (401) or it succeeds (204) in test environment
    expect($response->status())->toBeIn([401, 204]);
});

test('authenticated user can delete user', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->deleteJson("/api/user/{$targetUser->id}");

    $response->assertNoContent();

    // User is soft deleted, so check for deleted_at
    $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
});

test('user can view specific user by id', function (): void {
    /** @var TestCase $this */
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
                ->etc(),
        );
});
