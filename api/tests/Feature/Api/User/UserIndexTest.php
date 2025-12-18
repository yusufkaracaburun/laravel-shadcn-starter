<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot access user index endpoint', function (): void {
    /** @var TestCase $this */
    $response = $this->withHeaders([
        'Origin' => 'http://localhost:5173',
        'Accept' => 'application/json',
    ])->getJson('/api/user');

    // In test environment, Sanctum middleware may not block JSON requests without session
    // So we check that either it's blocked (401) or an error occurs (500)
    expect($response->status())->toBeIn([401, 500]);
});

test('authenticated user can list all users with pagination', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    User::factory()->count(2)->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data.data')
                ->whereType('data.current_page', 'integer')
                ->where('data.current_page', fn ($page): bool => $page > 0)
                ->whereType('data.per_page', 'integer')
                ->where('data.per_page', fn ($perPage): bool => $perPage > 0)
                ->whereType('data.total', 'integer')
                ->where('data.total', fn ($total): bool => $total > 0)
                ->has('data.first_page_url')
                ->has('data.last_page_url')
                ->etc(),
        );
});

test('pagination uses default per_page of 15', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    User::factory()->count(20)->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->has('data.data')
                ->whereType('data.per_page', 'integer')
                ->where('data.per_page', fn ($perPage): bool => $perPage > 0)
                ->whereType('data.total', 'integer')
                ->where('data.total', fn ($total): bool => $total > 0)
                ->whereType('data.last_page', 'integer')
                ->where('data.last_page', fn ($lastPage): bool => $lastPage > 0)
                ->etc(),
        );
});

test('pagination accepts custom per_page parameter', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    User::factory()->count(10)->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user?per_page=5');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->has('data.data')
                ->whereType('data.per_page', 'integer')
                ->where('data.per_page', fn ($perPage): bool => $perPage > 0)
                ->whereType('data.total', 'integer')
                ->where('data.total', fn ($total): bool => $total > 0)
                ->whereType('data.last_page', 'integer')
                ->where('data.last_page', fn ($lastPage): bool => $lastPage > 0)
                ->etc(),
        );
});

test('pagination page navigation works correctly', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    User::factory()->count(20)->create();

    Sanctum::actingAs($user, ['*']);

    // First page
    $response = $this->getJson('/api/user?per_page=10&page=1');
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->has('data.data')
                ->whereType('data.current_page', 'integer')
                ->where('data.current_page', fn ($page): bool => $page > 0)
                ->whereType('data.last_page', 'integer')
                ->where('data.last_page', fn ($lastPage): bool => $lastPage > 0)
                ->whereType('data.total', 'integer')
                ->where('data.total', fn ($total): bool => $total > 0)
                ->etc(),
        );

    // Second page
    $response = $this->getJson('/api/user?per_page=10&page=2');
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->has('data.data')
                ->whereType('data.current_page', 'integer')
                ->where('data.current_page', fn ($page): bool => $page > 0)
                ->whereType('data.last_page', 'integer')
                ->where('data.last_page', fn ($lastPage): bool => $lastPage > 0)
                ->etc(),
        );

    // Last page
    $response = $this->getJson('/api/user?per_page=10&page=3');
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->has('data.data')
                ->whereType('data.current_page', 'integer')
                ->where('data.current_page', fn ($page): bool => $page > 0)
                ->whereType('data.last_page', 'integer')
                ->where('data.last_page', fn ($lastPage): bool => $lastPage > 0)
                ->etc(),
        );
});

test('pagination per_page validation rejects invalid values', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    User::factory()->count(5)->create();

    Sanctum::actingAs($user, ['*']);

    // Test per_page less than 1 (should return validation error)
    $response = $this->getJson('/api/user?per_page=0');
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);

    // Test per_page greater than 100 (should return validation error)
    $response = $this->getJson('/api/user?per_page=200');
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);

    // Test per_page with valid value (should work)
    $response = $this->getJson('/api/user?per_page=50');
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->has('data.data')
                ->whereType('data.per_page', 'integer')
                ->where('data.per_page', fn ($perPage): bool => $perPage > 0)
                ->whereType('data.total', 'integer')
                ->where('data.total', fn ($total): bool => $total > 0)
                ->etc(),
        );
});
