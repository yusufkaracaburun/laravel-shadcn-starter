<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot update projects', function (): void {
    /** @var TestCase $this */
    $project = Project::factory()->create();

    $response = $this->putJson("/api/project/{$project->id}", [
        'name' => 'Updated Project',
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can update project', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $project = Project::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $updateData = [
        'name' => 'Updated Project Name',
        'status' => 'completed',
        'progress' => 100,
    ];

    $response = $this->putJson("/api/project/{$project->id}", $updateData);

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', $updateData['name'])
                    ->where('status', $updateData['status'])
                    ->where('progress', $updateData['progress'])
                    ->etc())
                ->etc()
        );

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => $updateData['name'],
        'status' => $updateData['status'],
        'progress' => $updateData['progress'],
    ]);
});

test('project update requires valid data', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $project = Project::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson("/api/project/{$project->id}", [
        'status' => 'invalid-status',
        'category' => 'invalid-category',
        'progress' => 150, // Invalid: exceeds max
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'category', 'progress']);
});

test('project update allows partial updates', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $project = Project::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson("/api/project/{$project->id}", [
        'name' => 'Updated Name Only',
    ]);

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', 'Updated Name Only')
                    ->etc())
                ->etc()
        );
});

test('project update is team-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create teams for users
    $team1 = Team::factory()->create(['user_id' => $user1->id]);
    $team2 = Team::factory()->create(['user_id' => $user2->id]);

    $user1->update(['current_team_id' => $team1->id]);
    $user2->update(['current_team_id' => $team2->id]);

    $project = Project::factory()->create(['team_id' => $team1->id]);

    Sanctum::actingAs($user2, ['*']);

    $response = $this->putJson("/api/project/{$project->id}", [
        'name' => 'Updated Name',
    ]);

    // Should return 404 or 403 because project belongs to different team
    expect($response->status())->toBeIn([404, 403]);
});
