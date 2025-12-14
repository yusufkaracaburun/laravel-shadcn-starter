<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot view project', function (): void {
    /** @var TestCase $this */
    $project = Project::factory()->create();

    $response = $this->getJson("/api/project/{$project->id}");

    $response->assertUnauthorized();
});

test('authenticated user can view project', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $project = Project::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/project/{$project->id}");

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('id', $project->id)
                    ->where('name', $project->name)
                    ->where('status', $project->status)
                    ->has('created_at')
                    ->has('updated_at')
                    ->etc())
                ->has('extra')
                ->etc()
        );
});

test('project show returns 404 for non-existent project', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/project/999999');

    $response->assertNotFound();
});

test('project show is team-scoped', function (): void {
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

    $response = $this->getJson("/api/project/{$project->id}");

    // Should return 404 because project belongs to different team
    $response->assertNotFound();
});
