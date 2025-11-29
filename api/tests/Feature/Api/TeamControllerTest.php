<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can list their teams', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team1 = Team::factory()->create(['user_id' => $user->id]);
    $team2 = Team::factory()->create();
    $team2->users()->attach($user->id, ['role' => 'member']);

    Sanctum::actingAs($user);

    // Act
    $response = $this->getJson('/api/teams');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'personal_team', 'user_id'],
            ],
        ]);

    $teams = $response->json('data');
    expect(count($teams))->toBeGreaterThanOrEqual(2);
});

test('authenticated user can create a team', function (): void {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Act
    $response = $this->postJson('/api/teams', [
        'name' => 'New Team',
        'personal_team' => false,
    ]);

    // Assert
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'personal_team', 'user_id'],
        ]);

    expect(\App\Models\Team::query()->where('name', 'New Team')->exists())->toBeTrue();
    $team = \App\Models\Team::query()->where('name', 'New Team')->first();
    expect($team->user_id)->toBe($user->id);
    expect($user->teams()->where('teams.id', $team->id)->exists())->toBeTrue();
});

test('authenticated user can view their team', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    Sanctum::actingAs($user);

    // Act
    $response = $this->getJson("/api/teams/{$team->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $team->id,
                'name' => $team->name,
            ],
        ]);
});

test('authenticated user can update their team', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    Sanctum::actingAs($user);

    // Act
    $response = $this->putJson("/api/teams/{$team->id}", [
        'name' => 'Updated Team Name',
    ]);

    // Assert
    $response->assertStatus(200);

    $team->refresh();
    expect($team->name)->toBe('Updated Team Name');
});

test('user cannot update team they do not own', function (): void {
    // Arrange
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    Sanctum::actingAs($otherUser);

    // Act
    $response = $this->putJson("/api/teams/{$team->id}", [
        'name' => 'Hacked Team Name',
    ]);

    // Assert
    $response->assertStatus(403);
});

test('authenticated user can delete their team', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    Sanctum::actingAs($user);

    // Act
    $response = $this->deleteJson("/api/teams/{$team->id}");

    // Assert
    $response->assertStatus(204);

    expect(\App\Models\Team::query()->find($team->id))->toBeNull();
});

test('authenticated user can switch current team', function (): void {
    // Arrange
    $user = User::factory()->create();
    $team1 = Team::factory()->create(['user_id' => $user->id]);
    $team2 = Team::factory()->create();
    $team2->users()->attach($user->id, ['role' => 'member']);
    Sanctum::actingAs($user);

    // Act
    $response = $this->putJson('/api/current-team', [
        'team_id' => $team2->id,
    ]);

    // Assert
    $response->assertStatus(200);

    $user->refresh();
    expect($user->current_team_id)->toBe($team2->id);
});

test('user cannot switch to team they do not belong to', function (): void {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $otherUser->id]);
    Sanctum::actingAs($user);

    // Act
    $response = $this->putJson('/api/current-team', [
        'team_id' => $team->id,
    ]);

    // Assert
    $response->assertStatus(403);
});
