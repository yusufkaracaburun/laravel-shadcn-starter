<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;

test('unauthenticated users cannot delete projects', function (): void {
    /** @var TestCase $this */
    $project = Project::factory()->create();

    $response = $this->deleteJson("/api/project/{$project->id}");

    $response->assertUnauthorized();
});

test('authenticated user can delete project', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $project = Project::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->deleteJson("/api/project/{$project->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});

test('project deletion is team-scoped', function (): void {
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

    $response = $this->deleteJson("/api/project/{$project->id}");

    // Should return 404 or 403 because project belongs to different team
    expect($response->status())->toBeIn([404, 403]);

    // Project should still exist
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
    ]);
});
