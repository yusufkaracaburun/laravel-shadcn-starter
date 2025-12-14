<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot list projects', function (): void {
    /** @var TestCase $this */
    $response = $this->getJson('/api/project');

    $response->assertUnauthorized();
});

test('authenticated user can list projects', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $project = Project::factory()->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/project');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'code',
            'message',
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'description', 'status', 'category', 'start_date', 'end_date', 'progress', 'team_id', 'created_at', 'updated_at'],
                ],
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
            'extra',
        ]);
});

test('projects list supports pagination', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    Project::factory()->count(15)->create(['team_id' => $user->current_team_id]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/project?page=1&per_page=10');

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data.data', 10)
                ->where('data.current_page', 1)
                ->where('data.per_page', 10)
                ->where('data.total', 15)
                ->etc()
        );
});

test('projects list is team-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create teams for users
    $team1 = Team::factory()->create(['user_id' => $user1->id]);
    $team2 = Team::factory()->create(['user_id' => $user2->id]);

    $user1->update(['current_team_id' => $team1->id]);
    $user2->update(['current_team_id' => $team2->id]);

    $project1 = Project::factory()->create(['team_id' => $team1->id]);
    $project2 = Project::factory()->create(['team_id' => $team2->id]);

    Sanctum::actingAs($user1, ['*']);

    $response = $this->getJson('/api/project');

    $response->assertOk();

    $data = $response->json('data.data');
    $projectIds = collect($data)->pluck('id')->toArray();

    expect($projectIds)->toContain($project1->id);
    expect($projectIds)->not->toContain($project2->id);
});
