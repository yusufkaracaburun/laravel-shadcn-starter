<?php

declare(strict_types=1);

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

test('unauthenticated users cannot list teams', function (): void {
    /** @var TestCase $this */
    $response = $this->getJson('/api/teams');

    $response->assertUnauthorized();
});

test('authenticated user can list teams', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $team1 = Team::factory()->create(['user_id' => $user->id]);
    $team1->users()->attach($user->id, ['role' => 'owner']);
    $team2 = Team::factory()->create();
    $team2->users()->attach($user->id, ['role' => 'member']);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/teams');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'code',
            'message',
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'personal_team', 'user_id', 'created_at', 'updated_at'],
                ],
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
            'extra',
        ]);
});

test('teams list supports pagination', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    Team::factory()->count(15)->create(['user_id' => $user->id])->each(function ($team) use ($user): void {
        $team->users()->attach($user->id, ['role' => 'owner']);
    });

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/teams?page=1&per_page=10');

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

test('teams list is user-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $team1 = Team::factory()->create(['user_id' => $user1->id]);
    $team1->users()->attach($user1->id, ['role' => 'owner']);
    $team2 = Team::factory()->create(['user_id' => $user2->id]);
    $team2->users()->attach($user2->id, ['role' => 'owner']);

    Sanctum::actingAs($user1, ['*']);

    $response = $this->getJson('/api/teams');

    $response->assertOk();

    $data = $response->json('data.data');
    $teamIds = collect($data)->pluck('id')->toArray();

    expect($teamIds)->toContain($team1->id);
    expect($teamIds)->not->toContain($team2->id);
});

test('unauthenticated users cannot create teams', function (): void {
    /** @var TestCase $this */
    $response = $this->postJson('/api/teams', [
        'name' => 'Test Team',
        'personal_team' => false,
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can create new team', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $teamData = [
        'name' => 'Test Team',
        'personal_team' => false,
    ];

    $response = $this->postJson('/api/teams', $teamData);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 201)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', $teamData['name'])
                    ->where('personal_team', $teamData['personal_team'])
                    ->etc())
                ->has('extra')
                ->etc()
        );

    $this->assertDatabaseHas('teams', [
        'name' => $teamData['name'],
        'user_id' => $user->id,
    ]);
});

test('team creation requires valid data', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/teams', [
        'name' => '',
        'personal_team' => 'invalid',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'personal_team']);
});

test('team can be created with optional fields', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/teams', [
        'name' => 'Test Team',
    ]);

    $response->assertCreated()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 201)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', 'Test Team')
                    ->where('personal_team', false)
                    ->etc())
                ->etc()
        );
});

test('unauthenticated users cannot view team', function (): void {
    /** @var TestCase $this */
    $team = Team::factory()->create();

    $response = $this->getJson("/api/teams/{$team->id}");

    $response->assertUnauthorized();
});

test('authenticated user can view team', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $team->users()->attach($user->id, ['role' => 'owner']);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/teams/{$team->id}");

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('id', $team->id)
                    ->where('name', $team->name)
                    ->has('created_at')
                    ->has('updated_at')
                    ->etc())
                ->has('extra')
                ->etc()
        );
});

test('team show returns 404 for non-existent team', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/teams/999999');

    $response->assertNotFound();
});

test('team show is user-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $team = Team::factory()->create(['user_id' => $user1->id]);
    $team->users()->attach($user1->id, ['role' => 'owner']);

    Sanctum::actingAs($user2, ['*']);

    $response = $this->getJson("/api/teams/{$team->id}");

    // Should return 404 because team doesn't belong to user2
    $response->assertNotFound();
});

test('unauthenticated users cannot update teams', function (): void {
    /** @var TestCase $this */
    $team = Team::factory()->create();

    $response = $this->putJson("/api/teams/{$team->id}", [
        'name' => 'Updated Team',
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can update team', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $team->users()->attach($user->id, ['role' => 'owner']);

    Sanctum::actingAs($user, ['*']);

    $updateData = [
        'name' => 'Updated Team Name',
        'personal_team' => true,
    ];

    $response = $this->putJson("/api/teams/{$team->id}", $updateData);

    $response->assertOk()
        ->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->where('success', true)
                ->where('code', 200)
                ->has('data', fn (AssertableJson $json): AssertableJson => $json->where('name', $updateData['name'])
                    ->where('personal_team', $updateData['personal_team'])
                    ->etc())
                ->etc()
        );

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => $updateData['name'],
        'personal_team' => $updateData['personal_team'],
    ]);
});

test('team update requires valid data', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $team->users()->attach($user->id, ['role' => 'owner']);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson("/api/teams/{$team->id}", [
        'name' => '',
        'personal_team' => 'invalid',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'personal_team']);
});

test('team update allows partial updates', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $team->users()->attach($user->id, ['role' => 'owner']);

    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson("/api/teams/{$team->id}", [
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

test('team update is user-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $team = Team::factory()->create(['user_id' => $user1->id]);
    $team->users()->attach($user1->id, ['role' => 'owner']);

    Sanctum::actingAs($user2, ['*']);

    $response = $this->putJson("/api/teams/{$team->id}", [
        'name' => 'Updated Name',
    ]);

    // Should return 404 or 403 because team doesn't belong to user2
    expect($response->status())->toBeIn([404, 403]);
});

test('unauthenticated users cannot delete teams', function (): void {
    /** @var TestCase $this */
    $team = Team::factory()->create();

    $response = $this->deleteJson("/api/teams/{$team->id}");

    $response->assertUnauthorized();
});

test('authenticated user can delete team', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $team->users()->attach($user->id, ['role' => 'owner']);

    Sanctum::actingAs($user, ['*']);

    $response = $this->deleteJson("/api/teams/{$team->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('teams', [
        'id' => $team->id,
    ]);
});

test('team deletion is user-scoped', function (): void {
    /** @var TestCase $this */
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $team = Team::factory()->create(['user_id' => $user1->id]);
    $team->users()->attach($user1->id, ['role' => 'owner']);

    Sanctum::actingAs($user2, ['*']);

    $response = $this->deleteJson("/api/teams/{$team->id}");

    // Should return 404 or 403 because team doesn't belong to user2
    expect($response->status())->toBeIn([404, 403]);

    // Team should still exist
    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
    ]);
});

test('authenticated user can switch current team', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $team1 = Team::factory()->create(['user_id' => $user->id]);
    $team1->users()->attach($user->id, ['role' => 'owner']);
    $team2 = Team::factory()->create();
    $team2->users()->attach($user->id, ['role' => 'member']);
    $user->update(['current_team_id' => $team1->id]);
    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson('/api/current-team', [
        'team_id' => $team2->id,
    ]);

    $response->assertOk();

    $user->refresh();
    expect($user->current_team_id)->toBe($team2->id);
});

test('user cannot switch to team they do not belong to', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $otherUser->id]);
    $otherUser->teams()->attach($team->id, ['role' => 'owner']);
    $userTeam = Team::factory()->create(['user_id' => $user->id]);
    $user->teams()->attach($userTeam->id, ['role' => 'owner']);
    $user->update(['current_team_id' => $userTeam->id]);
    Sanctum::actingAs($user, ['*']);

    $response = $this->putJson('/api/current-team', [
        'team_id' => $team->id,
    ]);

    $response->assertStatus(403);
});
