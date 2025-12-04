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

    $response->assertUnauthorized();
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
                ->has('data.data', 3)
                ->has('data.data.0', fn (AssertableJson $json): AssertableJson => $json->has('id')
                    ->has('name')
                    ->has('email')
                    ->etc())
                ->where('data.current_page', 1)
                ->where('data.last_page', 1)
                ->where('data.per_page', 15)
                ->where('data.total', 3)
                ->has('data.path')
                ->has('data.first_page_url')
                ->has('data.last_page_url')
                ->etc()
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
                ->has('data.data', 15)
                ->where('data.per_page', 15)
                ->where('data.total', 21)
                ->where('data.last_page', 2)
                ->etc()
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
                ->has('data.data', 5)
                ->where('data.per_page', 5)
                ->where('data.total', 11)
                ->where('data.last_page', 3)
                ->etc()
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
                ->has('data.data', 10)
                ->where('data.current_page', 1)
                ->where('data.last_page', 3)
                ->where('data.total', 21)
                ->etc()
        );

    // Second page
    $response = $this->getJson('/api/user?per_page=10&page=2');
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->has('data.data', 10)
                ->where('data.current_page', 2)
                ->where('data.last_page', 3)
                ->etc()
        );

    // Last page
    $response = $this->getJson('/api/user?per_page=10&page=3');
    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->has('data.data', 1)
                ->where('data.current_page', 3)
                ->where('data.last_page', 3)
                ->etc()
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
            fn (AssertableJson $json): AssertableJson => $json->where('data.per_page', 50)
                ->etc()
        );
});
