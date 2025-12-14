<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot create projects', function (): void {
    /** @var TestCase $this */
    $response = $this->postJson('/api/project', [
        'name' => 'Test Project',
        'status' => 'active',
        'category' => 'development',
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can create new project', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $projectData = [
        'name' => 'Test Project',
        'description' => 'Test project description',
        'status' => 'active',
        'category' => 'development',
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'progress' => 50,
    ];

    $response = $this->postJson('/api/project', $projectData);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 201)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', $projectData['name'])
                    ->where('description', $projectData['description'])
                    ->where('status', $projectData['status'])
                    ->where('category', $projectData['category'])
                    ->where('progress', $projectData['progress'])
                    ->etc())
                ->has('extra')
                ->etc()
        );

    $this->assertDatabaseHas('projects', [
        'name' => $projectData['name'],
        'status' => $projectData['status'],
        'team_id' => $user->current_team_id,
    ]);
});

test('project creation requires valid data', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/project', [
        'name' => '',
        'status' => 'invalid-status',
        'category' => 'invalid-category',
        'progress' => 150, // Invalid: exceeds max
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'status', 'category', 'progress']);
});

test('project can be created with optional fields', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/project', [
        'name' => 'Test Project',
        'status' => 'active',
        'category' => 'design',
    ]);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 201)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', 'Test Project')
                    ->where('description', null)
                    ->where('start_date', null)
                    ->where('end_date', null)
                    ->etc())
                ->etc()
        );
});

test('project end_date must be after or equal to start_date', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/project', [
        'name' => 'Test Project',
        'status' => 'active',
        'category' => 'development',
        'start_date' => '2024-12-31',
        'end_date' => '2024-01-01', // Before start_date
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['end_date']);
});
